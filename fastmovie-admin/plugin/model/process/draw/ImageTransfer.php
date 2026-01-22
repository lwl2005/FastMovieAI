<?php

namespace plugin\model\process\draw;

use app\expose\enum\State;
use plugin\control\expose\helper\Uploads;
use plugin\control\utils\yidevs\Yidevs;
use plugin\model\app\model\PluginModelTask;
use plugin\model\app\model\PluginModelTaskResult;
use plugin\model\utils\enum\ModelScene;
use plugin\model\utils\enum\ModelTaskStatus;
use plugin\model\utils\enum\ModelType;
use plugin\notification\expose\helper\Push;
use plugin\shortplay\app\model\PluginShortplayActor;
use plugin\shortplay\app\model\PluginShortplayActorCharacterLook;
use plugin\shortplay\app\model\PluginShortplayCharacterLook;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\shortplay\app\model\PluginShortplayDramaActor;
use plugin\shortplay\app\model\PluginShortplayDramaEpisodeActor;
use plugin\shortplay\app\model\PluginShortplayDramaScene;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboard;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboardActor;
use plugin\shortplay\app\model\PluginShortplayProp;
use plugin\shortplay\utils\enum\ActorStatus;
use plugin\shortplay\utils\enum\PropStatus;
use support\Log;
use think\facade\Db;
use Workerman\Coroutine;
use Workerman\Crontab\Crontab;
use Workerman\Timer;

class ImageTransfer
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
                    $PluginModelTask = PluginModelTask::where(['status' => ModelTaskStatus::WAIT_DOWNLOAD['value'], 'model_type' => ModelType::DRAW['value']])->order('last_heartbeat asc,id asc')->lock(true)->find();
                    if (!$PluginModelTask) {
                        return;
                    }
                    $PluginModelTask->status = ModelTaskStatus::DOWNLOADING['value'];
                    $PluginModelTask->last_heartbeat = date('Y-m-d H:i:s', strtotime('+5 seconds'));
                    $PluginModelTask->save();
                    $event = 'generate' . strtolower(str_replace('_', '', $PluginModelTask->scene));
                    $pushData = [
                        'task_id' => $PluginModelTask->id,
                        'id' => $PluginModelTask->alias_id
                    ];
                    $PluginModelTaskResult = PluginModelTaskResult::where('task_id', $PluginModelTask->id)->find();
                    if (!$PluginModelTaskResult) {
                        $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                        $PluginModelTask->status = ModelTaskStatus::FAIL['value'];
                        $PluginModelTask->save();
                        Push::send([
                            'uid' => $PluginModelTask->uid,
                            'channels_uid' => $PluginModelTask->channels_uid,
                            'event' => $event
                        ], $pushData);
                        return;
                    }
                    try {
                        $ModelScene = ModelScene::get($PluginModelTask->scene);
                        $classify = Uploads::getClassify($PluginModelTask->channels_uid, 'uploads/' . $PluginModelTask->scene, $ModelScene['label']);
                        $result = Uploads::download($PluginModelTask->channels_uid, $PluginModelTaskResult->image, $classify);
                    } catch (\Throwable $th) {
                        $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                        $PluginModelTask->status = ModelTaskStatus::WAIT_DOWNLOAD['value'];
                        $PluginModelTask->save();
                        Log::error("图片转存处理失败：" . $th->getMessage(), $th->getTrace());
                        return;
                    }
                    $pushData['image'] = $result->file_url;
                    Db::startTrans();
                    try {
                        $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                        $PluginModelTask->status = ModelTaskStatus::SUCCESS['value'];
                        $PluginModelTask->save();
                        $PluginModelTaskResult = PluginModelTaskResult::where('task_id', $PluginModelTask->id)->find();
                        $PluginModelTaskResult->image_path = $result->file_name;
                        $PluginModelTaskResult->save();
                        switch ($PluginModelTask->scene) {
                            case ModelScene::DRAMA_COVER['value']:
                                $PluginShortplayDrama = PluginShortplayDrama::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayDrama->cover = $result->file_name;
                                $PluginShortplayDrama->save();
                                break;
                            case ModelScene::SCENE_IMAGE['value']:
                                $PluginShortplayDramaScene = PluginShortplayDramaScene::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayDramaScene->image = $result->file_name;
                                $PluginShortplayDramaScene->save();
                                break;
                            case ModelScene::ACTOR_IMAGE['value']:
                                $PluginShortplayActor = PluginShortplayActor::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayActor->headimg = $result->file_name;
                                $PluginShortplayActor->status = $PluginShortplayActor->headimg && $PluginShortplayActor->three_view_image ? ActorStatus::GENERATED['value'] : ActorStatus::INITIALIZING['value'];
                                $PluginShortplayActor->save();
                                $threeViewImageState = PluginModelTask::processing(['alias_id' => $PluginShortplayActor->id, 'scene' => ModelScene::ACTOR_THREE_VIEW_IMAGE['value']]);
                                if ($threeViewImageState > 0) {
                                    $pushData['status'] = ActorStatus::PENDING;
                                } else {
                                    $pushData['status'] = ActorStatus::get($PluginShortplayActor->status);
                                }
                                break;
                            case ModelScene::ACTOR_THREE_VIEW_IMAGE['value']:
                                $PluginShortplayActor = PluginShortplayActor::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayActor->three_view_image = $result->file_name;
                                $PluginShortplayActor->status = $PluginShortplayActor->headimg && $PluginShortplayActor->three_view_image ? ActorStatus::GENERATED['value'] : ActorStatus::INITIALIZING['value'];
                                $PluginShortplayActor->save();
                                $pushData['status'] = ActorStatus::get($PluginShortplayActor->status);
                                break;
                            case ModelScene::STORYBOARD_IMAGE['value']:
                                $PluginShortplayDramaStoryboard = PluginShortplayDramaStoryboard::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayDramaStoryboard->image = $result->file_name;
                                $PluginShortplayDramaStoryboard->use_material_type='image';
                                $PluginShortplayDramaStoryboard->save();
                                $event = 'generatestoryboard';
                                $pushData['event'] = ModelScene::STORYBOARD_IMAGE['value'];
                                break;
                            case ModelScene::ACTOR_COSTUME['value']:
                                $PluginShortplayActorCharacterLook = PluginShortplayActorCharacterLook::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayActorCharacterLook->headimg = $result->file_name;
                                $PluginShortplayActorCharacterLook->save();
                                $event = 'generatestoryboard';
                                $pushData['storyboard_id'] = $PluginShortplayActorCharacterLook->storyboard_id;
                                $pushData['episode_id'] = $PluginShortplayActorCharacterLook->episode_id;
                                $pushData['drama_id'] = $PluginShortplayActorCharacterLook->drama_id;
                                $pushData['id'] = $PluginShortplayActorCharacterLook->actor_id;
                                $pushData['event'] = ModelScene::ACTOR_COSTUME['value'];
                                break;
                            case ModelScene::ACTOR_COSTUME_THREE_VIEW['value']:
                                $PluginShortplayActorCharacterLook = PluginShortplayActorCharacterLook::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayActorCharacterLook->three_view_image = $result->file_name;
                                $PluginShortplayActorCharacterLook->status = ActorStatus::GENERATED['value'];
                                $PluginShortplayActorCharacterLook->save();
                                $event = 'generatestoryboard';
                                $pushData['event'] = ModelScene::ACTOR_COSTUME_THREE_VIEW['value'];
                                $pushData['storyboard_id'] = $PluginShortplayActorCharacterLook->storyboard_id;
                                $pushData['episode_id'] = $PluginShortplayActorCharacterLook->episode_id;
                                $pushData['drama_id'] = $PluginShortplayActorCharacterLook->drama_id;
                                $pushData['id'] = $PluginShortplayActorCharacterLook->actor_id;

                                if ($PluginShortplayActorCharacterLook->storyboard_id) {
                                    $PluginShortplayDramaStoryboardActor = PluginShortplayDramaStoryboardActor::where(['storyboard_id' => $PluginShortplayActorCharacterLook->storyboard_id, 'actor_id' => $PluginShortplayActorCharacterLook->actor_id])->find();
                                    $PluginShortplayDramaStoryboardActor->character_look_id = $PluginShortplayActorCharacterLook->id;
                                    $PluginShortplayDramaStoryboardActor->headimg = $PluginShortplayActorCharacterLook->headimg;
                                    $PluginShortplayDramaStoryboardActor->three_view_image = $PluginShortplayActorCharacterLook->three_view_image;
                                    $PluginShortplayDramaStoryboardActor->save();
                                } elseif ($PluginShortplayActorCharacterLook->episode_id) {
                                    $PluginShortplayDramaEpisodeActor = PluginShortplayDramaEpisodeActor::where(['episode_id' => $PluginShortplayActorCharacterLook->episode_id, 'actor_id' => $PluginShortplayActorCharacterLook->actor_id])->find();
                                    $PluginShortplayDramaEpisodeActor->character_look_id = $PluginShortplayActorCharacterLook->id;
                                    $PluginShortplayDramaEpisodeActor->headimg = $PluginShortplayActorCharacterLook->headimg;
                                    $PluginShortplayDramaEpisodeActor->three_view_image = $PluginShortplayActorCharacterLook->three_view_image;
                                    $PluginShortplayDramaEpisodeActor->save();
                                } elseif ($PluginShortplayActorCharacterLook->drama_id) {
                                    $PluginShortplayDramaActor = PluginShortplayDramaActor::where(['drama_id' => $PluginShortplayActorCharacterLook->drama_id, 'actor_id' => $PluginShortplayActorCharacterLook->actor_id])->find();
                                    $PluginShortplayDramaActor->character_look_id = $PluginShortplayActorCharacterLook->id;
                                    $PluginShortplayDramaActor->headimg = $PluginShortplayActorCharacterLook->headimg;
                                    $PluginShortplayDramaActor->three_view_image = $PluginShortplayActorCharacterLook->three_view_image;
                                    $PluginShortplayDramaActor->save();
                                }
                                break;
                            case ModelScene::PROP_IMAGE['value']:
                                $PluginShortplayProp = PluginShortplayProp::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayProp->image = $result->file_name;
                                $PluginShortplayProp->status = $PluginShortplayProp->image && $PluginShortplayProp->three_view_image ? PropStatus::GENERATED['value'] : PropStatus::INITIALIZING['value'];
                                $PluginShortplayProp->save();
                                $threeViewImageState = PluginModelTask::processing(['alias_id' => $PluginShortplayProp->id, 'scene' => ModelScene::PROP_THREE_VIEW_IMAGE['value']]);
                                if ($threeViewImageState > 0) {
                                    $pushData['status'] = PropStatus::PENDING;
                                } else {
                                    $pushData['status'] = PropStatus::get($PluginShortplayProp->status);
                                }
                                break;
                            case ModelScene::PROP_THREE_VIEW_IMAGE['value']:
                                $PluginShortplayProp = PluginShortplayProp::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayProp->three_view_image = $result->file_name;
                                $PluginShortplayProp->status = $PluginShortplayProp->image && $PluginShortplayProp->three_view_image ? PropStatus::GENERATED['value'] : PropStatus::INITIALIZING['value'];
                                $PluginShortplayProp->save();
                                $pushData['status'] = PropStatus::get($PluginShortplayProp->status);
                                break;
                            case ModelScene::CHARACTER_LOOK_COSTUME['value']:
                                $PluginShortplayCharacterLook = PluginShortplayCharacterLook::where('id', $PluginModelTask->alias_id)->find();
                                $PluginShortplayCharacterLook->costume_url = $result->file_name;
                                $PluginShortplayCharacterLook->status =  PropStatus::GENERATED['value'];
                                $PluginShortplayCharacterLook->save();
                                $pushData['status'] = PropStatus::get($PluginShortplayCharacterLook->status);
                                break;
                        }
                        Db::commit();
                    } catch (\Throwable $th) {
                        Db::rollback();
                        Log::error("图片转存保存失败：" . $th->getMessage(), $th->getTrace());
                        return;
                    }
                    Push::send([
                        'uid' => $PluginModelTask->uid,
                        'channels_uid' => $PluginModelTask->channels_uid,
                        'event' => $event
                    ], $pushData);
                    switch ($PluginModelTask->scene) {
                        case ModelScene::ACTOR_IMAGE['value']:
                            try {
                                $PluginModelTask = PluginModelTask::where(['pre_task_id' => $PluginModelTask->id, 'status' => ModelTaskStatus::WAIT['value']])->with(['result'])->find();
                                if ($PluginModelTask) {
                                    $data = $PluginModelTask->result->params;
                                    $data['form_data']['images'] = [$result->file_url];
                                    $result = Yidevs::DrawAssistantTIGI($PluginModelTask->channels_uid, $data);
                                    $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                                    $PluginModelTask->task_id = $result['task_id'];
                                    $PluginModelTask->status = ModelTaskStatus::PROCESSING['value'];
                                    $PluginModelTask->save();
                                }
                            } catch (\Throwable $e) {
                                $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                                $PluginModelTask->pre_task_status = ModelTaskStatus::SUCCESS['value'];
                                $PluginModelTask->save();
                                Log::error('GenerateActorThreeView Error:' . $e->getMessage());
                            }
                            break;
                        case ModelScene::PROP_IMAGE['value']:
                            try {
                                $PluginModelTask = PluginModelTask::where(['pre_task_id' => $PluginModelTask->id, 'status' => ModelTaskStatus::WAIT['value']])->with(['result'])->find();
                                if ($PluginModelTask) {
                                    $data = $PluginModelTask->result->params;
                                    $data['form_data']['images'] = [$result->file_url];
                                    $result = Yidevs::DrawAssistantTIGI($PluginModelTask->channels_uid, $data);
                                    $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                                    $PluginModelTask->task_id = $result['task_id'];
                                    $PluginModelTask->status = ModelTaskStatus::PROCESSING['value'];
                                    $PluginModelTask->save();
                                }
                            } catch (\Throwable $e) {
                                $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                                $PluginModelTask->pre_task_status = ModelTaskStatus::SUCCESS['value'];
                                $PluginModelTask->save();
                                Log::error('GeneratePropThreeView Error:' . $e->getMessage());
                            }
                            break;
                        case ModelScene::ACTOR_COSTUME['value']:
                            try {
                                $PluginModelTask = PluginModelTask::where(['pre_task_id' => $PluginModelTask->id, 'status' => ModelTaskStatus::WAIT['value']])->with(['result'])->find();
                                if ($PluginModelTask) {
                                    $data = $PluginModelTask->result->params;
                                    $data['form_data']['images'] = [$result->file_url];
                                    $result = Yidevs::DrawAssistantTIGI($PluginModelTask->channels_uid, $data);
                                    $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                                    $PluginModelTask->task_id = $result['task_id'];
                                    $PluginModelTask->status = ModelTaskStatus::PROCESSING['value'];
                                    $PluginModelTask->save();
                                }
                            } catch (\Throwable $e) {
                                $PluginModelTask = PluginModelTask::where('id', $PluginModelTask->id)->find();
                                $PluginModelTask->pre_task_status = ModelTaskStatus::SUCCESS['value'];
                                $PluginModelTask->save();
                                Log::error('GenerateActorThreeView Error:' . $e->getMessage());
                            }
                            break;
                    }
                });
            } catch (\Throwable $th) {
                Log::error("图片转存定时任务失败：" . $th->getMessage(), $th->getTrace());
            }
        });
    }
}
