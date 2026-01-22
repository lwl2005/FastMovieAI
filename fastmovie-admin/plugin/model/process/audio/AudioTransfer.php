<?php

namespace plugin\model\process\audio;

use plugin\control\expose\helper\Uploads;
use plugin\model\app\model\PluginModelTask;
use plugin\model\app\model\PluginModelTaskResult;
use plugin\model\utils\enum\ModelScene;
use plugin\model\utils\enum\ModelTaskStatus;
use plugin\model\utils\enum\ModelType;
use plugin\notification\expose\helper\Push;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboard;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboardDialogue;
use support\Log;
use think\facade\Db;
use Workerman\Coroutine;
use Workerman\Crontab\Crontab;
use Workerman\Timer;

class AudioTransfer
{
    public function onWorkerStart($worker)
    {
        $id = $worker->id;
        new Crontab('*/5 * * * * *', function () use ($id) {
            try {
                if ($id) {
                    Timer::sleep(0.3 * $id);
                }
                Coroutine::create(function () {
                    $PluginModelTask = PluginModelTask::where(['status' => ModelTaskStatus::WAIT_DOWNLOAD['value'], 'model_type' => ModelType::AUDIO['value']])->order('last_heartbeat asc,id asc')->lock(true)->find();
                    if (!$PluginModelTask) {
                        return;
                    }
                    $PluginModelTask->status = ModelTaskStatus::DOWNLOADING['value'];
                    $PluginModelTask->last_heartbeat = date('Y-m-d H:i:s', strtotime('+5 seconds'));
                    $PluginModelTask->save();
                    $PluginModelTaskResult = PluginModelTaskResult::where('task_id', $PluginModelTask->id)->find();
                    if (!$PluginModelTaskResult) {
                        return;
                    }
                    try {
                        $ModelScene = ModelScene::get($PluginModelTask->scene);
                        $classify = Uploads::getClassify($PluginModelTask->channels_uid, 'uploads/' . $PluginModelTask->scene, $ModelScene['label']);
                        $result = Uploads::download($PluginModelTask->channels_uid, $PluginModelTaskResult->audio, $classify);
                    } catch (\Throwable $th) {
                        $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                        $PluginModelTask->status = ModelTaskStatus::WAIT_DOWNLOAD['value'];
                        $PluginModelTask->save();
                        Log::error("音频转存处理失败：" . $th->getMessage(), $th->getTrace());
                        return;
                    }
                    $pushData = [
                        'task_id' => $PluginModelTask->id,
                        'id' => $PluginModelTask->alias_id,
                        'audio' => $result->file_url
                    ];
                    $event = 'generate' . strtolower(str_replace('_', '', $PluginModelTask->scene));
                    Db::startTrans();
                    try {
                        $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                        $PluginModelTask->status = ModelTaskStatus::SUCCESS['value'];
                        $PluginModelTask->save();
                        $PluginModelTaskResult = PluginModelTaskResult::where('task_id', $PluginModelTask->id)->find();
                        $PluginModelTaskResult->audio_path = $result->file_name;
                        $PluginModelTaskResult->save();
                        switch ($PluginModelTask->scene) {
                            case ModelScene::DIALOGUE_VOICE['value']:
                                $PluginShortplayDramaStoryboardDialogue = PluginShortplayDramaStoryboardDialogue::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayDramaStoryboardDialogue->audio = $result->file_name;
                                $PluginShortplayDramaStoryboardDialogue->save();
                                $event = 'generatestoryboard';
                                $pushData['event'] = ModelScene::DIALOGUE_VOICE['value'];
                                $pushData['id']=$PluginShortplayDramaStoryboardDialogue->storyboard_id;
                                $pushData['dialogue_id']=$PluginShortplayDramaStoryboardDialogue->id;
                                break;
                            case ModelScene::STORYBOARD_NARRATION_VOICE['value']:
                                $PluginShortplayDramaStoryboard = PluginShortplayDramaStoryboard::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayDramaStoryboard->narration_audio = $result->file_name;
                                $PluginShortplayDramaStoryboard->save();
                                $event = 'generatestoryboard';
                                $pushData['event'] = ModelScene::STORYBOARD_NARRATION_VOICE['value'];
                                break;
                        }
                        Db::commit();
                    } catch (\Throwable $th) {
                        Db::rollback();
                        Log::error("音频转存保存失败：" . $th->getMessage(), $th->getTrace());
                    }
                    Push::send([
                        'uid' => $PluginModelTask->uid,
                        'channels_uid' => $PluginModelTask->channels_uid,
                        'event' => $event
                    ], $pushData);
                });
            } catch (\Throwable $th) {
                Log::error("音频转存定时任务失败：" . $th->getMessage(), $th->getTrace());
            }
        });
    }
}
