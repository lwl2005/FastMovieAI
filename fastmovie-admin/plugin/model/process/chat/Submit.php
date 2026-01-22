<?php

namespace plugin\model\process\chat;

use plugin\control\utils\yidevs\Yidevs;
use plugin\finance\expose\helper\Account;
use plugin\finance\utils\enum\PointsBillScene;
use plugin\model\app\model\PluginModel;
use plugin\model\app\model\PluginModelTask;
use plugin\model\app\model\PluginModelTaskResult;
use plugin\model\utils\enum\ModelScene;
use plugin\model\utils\enum\ModelTaskStatus;
use plugin\model\utils\enum\ModelType;
use plugin\notification\expose\helper\Push;
use plugin\shortplay\app\model\PluginShortplayActor;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\shortplay\app\model\PluginShortplayDramaActor;
use plugin\shortplay\app\model\PluginShortplayDramaEpisode;
use plugin\shortplay\app\model\PluginShortplayDramaEpisodeActor;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboard;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboardActor;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboardDialogue;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboardProp;
use plugin\shortplay\app\model\PluginShortplayProp;
use plugin\shortplay\utils\enum\ActorStatus;
use plugin\shortplay\utils\enum\PropStatus;
use support\Log;
use think\facade\Db;
use Workerman\Coroutine;
use Workerman\Crontab\Crontab;
use Workerman\Timer;

class Submit
{
    public function onWorkerStart($worker)
    {
        $id = $worker->id;
        new Crontab('*/5 * * * * *', function () use (&$id) {
            try {
                if ($id) {
                    Timer::sleep(0.3 * $id);
                }
                Coroutine::create(function () {
                    $PluginModelTask = PluginModelTask::where(['status' => ModelTaskStatus::WAIT['value'], 'model_type' => ModelType::CHAT['value']])->order('last_heartbeat asc,id asc')->lock(true)->find();
                    if (!$PluginModelTask) {
                        return;
                    }
                    if ($PluginModelTask->expectation_execution_count !== null) {
                        $PluginModelTask->execution_count = Db::raw('execution_count + 1');
                    }
                    $PluginModelTask->status = ModelTaskStatus::PROCESSING['value'];
                    $PluginModelTask->last_heartbeat = date('Y-m-d H:i:s', strtotime('+5 seconds'));
                    $PluginModelTask->save();
                    p($PluginModelTask->id);
                    $event = 'generate' . strtolower(str_replace('_', '', $PluginModelTask->scene));
                    $PluginModelTaskResult = PluginModelTaskResult::where('task_id', $PluginModelTask->id)->find();
                    if (!$PluginModelTaskResult) {
                        $PluginModelTask->status = ModelTaskStatus::FAIL['value'];
                        $PluginModelTask->save();
                        Push::send([
                            'uid' => $PluginModelTask->uid,
                            'channels_uid' => $PluginModelTask->channels_uid,
                            'event' => $event
                        ], [
                            'task_id' => $PluginModelTask->id,
                            'id' => $PluginModelTask->alias_id
                        ]);
                        return;
                    }
                    $data = $PluginModelTaskResult->params;
                    switch ($PluginModelTask->scene) {
                        case ModelScene::CREATIVE_EPISODE['value']:
                            try {
                                $data['params']['form_data']['episode_no'] = $data['params']['form_data']['episode_no'] + 1;
                                p($PluginModelTask->id, $data['params']['form_data']['episode_no'], 'start');
                                $result = Yidevs::ChatAssistantCompletions($data['channels_uid'], $data['params']);
                                p($PluginModelTask->id, $data['params']['form_data']['episode_no'], 'success');
                            } catch (\Throwable $th) {
                                Log::error('CREATIVE_EPISODE Error:' . $th->getMessage() . PHP_EOL . $th->getTraceAsString());
                            }
                            $taksStatus = ModelTaskStatus::FAIL['value'];
                            if (!empty($result)) {
                                Db::startTrans();
                                try {
                                    $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $data['drama_id']])->find();
                                    $episode_num = $PluginShortplayDrama->episode_num + 1;
                                    $PluginShortplayDrama->episode_num = Db::raw('episode_num + 1');
                                    $PluginShortplayDrama->save();
                                    $PluginShortplayDramaEpisode = new PluginShortplayDramaEpisode();
                                    $PluginShortplayDramaEpisode->channels_uid = $data['channels_uid'];
                                    $PluginShortplayDramaEpisode->drama_id = $data['drama_id'];
                                    $PluginShortplayDramaEpisode->episode_no = $episode_num;
                                    $PluginShortplayDramaEpisode->title = $result['title'];
                                    $PluginShortplayDramaEpisode->outline = $result['description'];
                                    $PluginShortplayDramaEpisode->content = $result['content'];
                                    $PluginShortplayDramaEpisode->save();
                                    if (!empty($result['actor'])) {
                                        $PluginShortplayActors = PluginShortplayActor::whereIn('actor_id', $result['actor'])->field('id,actor_id')->select();
                                        foreach ($PluginShortplayActors as $PluginShortplayActor) {
                                            if (!PluginShortplayDramaActor::where(['drama_id' => $PluginShortplayDrama->id, 'actor_id' => $PluginShortplayActor->id])->count()) {
                                                $PluginShortplayDramaActor = new PluginShortplayDramaActor();
                                                $PluginShortplayDramaActor->channels_uid = $data['channels_uid'];
                                                $PluginShortplayDramaActor->drama_id = $PluginShortplayDrama->id;
                                                $PluginShortplayDramaActor->actor_id = $PluginShortplayActor->id;
                                                $PluginShortplayDramaActor->save();
                                            }
                                            $PluginShortplayDramaEpisodeActor = new PluginShortplayDramaEpisodeActor();
                                            $PluginShortplayDramaEpisodeActor->channels_uid = $data['channels_uid'];
                                            $PluginShortplayDramaEpisodeActor->drama_id = $PluginShortplayDrama->id;
                                            $PluginShortplayDramaEpisodeActor->episode_id = $PluginShortplayDramaEpisode->id;
                                            $PluginShortplayDramaEpisodeActor->actor_id = $PluginShortplayActor->id;
                                            $PluginShortplayDramaEpisodeActor->save();
                                        }
                                    }
                                    if (!empty($result['create_actor'])) {
                                        foreach ($result['create_actor'] as $actor) {
                                            $PluginShortplayActor = PluginShortplayActor::where(['name' => $actor['name'], 'channels_uid' => $data['channels_uid'], 'drama_id' => $PluginShortplayDrama->id])->field('id,actor_id')->find();
                                            if (!$PluginShortplayActor) {
                                                $PluginShortplayActor = new PluginShortplayActor();
                                                $PluginShortplayActor->channels_uid = $data['channels_uid'];
                                                $PluginShortplayActor->drama_id = $PluginShortplayDrama->id;
                                                $PluginShortplayActor->episode_id = $PluginShortplayDramaEpisode->id;
                                                $PluginShortplayActor->uid = $data['uid'];
                                                $PluginShortplayActor->actor_id = uniqid();
                                                $PluginShortplayActor->name = $actor['name'];
                                                $PluginShortplayActor->species_type = $actor['species'];
                                                $PluginShortplayActor->gender = $actor['gender'];
                                                $PluginShortplayActor->age = $actor['age'];
                                                $PluginShortplayActor->remarks = $actor['description'];
                                                $PluginShortplayActor->status = ActorStatus::INITIALIZING['value'];
                                                $PluginShortplayActor->save();
                                            }
                                            $PluginShortplayDramaActor = new PluginShortplayDramaActor();
                                            $PluginShortplayDramaActor->channels_uid = $data['channels_uid'];
                                            $PluginShortplayDramaActor->drama_id = $PluginShortplayDrama->id;
                                            $PluginShortplayDramaActor->actor_id = $PluginShortplayActor->id;
                                            $PluginShortplayDramaActor->save();
                                            $PluginShortplayDramaEpisodeActor = new PluginShortplayDramaEpisodeActor();
                                            $PluginShortplayDramaEpisodeActor->channels_uid = $data['channels_uid'];
                                            $PluginShortplayDramaEpisodeActor->drama_id = $PluginShortplayDrama->id;
                                            $PluginShortplayDramaEpisodeActor->episode_id = $PluginShortplayDramaEpisode->id;
                                            $PluginShortplayDramaEpisodeActor->actor_id = $PluginShortplayActor->id;
                                            $PluginShortplayDramaEpisodeActor->save();
                                        }
                                    }
                                    $PluginModelTask = PluginModelTask::where(['id' => $PluginModelTask->id])->with(['result'])->find();
                                    if (
                                        $PluginModelTask->expectation_execution_count === null
                                        || ($PluginModelTask->expectation_execution_count !== null && $PluginModelTask->success_execution_count + 1 >= $PluginModelTask->expectation_execution_count)
                                    ) {
                                        $PluginModelTask->status = ModelTaskStatus::SUCCESS['value'];
                                        $PluginModelTask->success_execution_count = Db::raw('success_execution_count + 1');
                                        $PluginModelTask->result->result = $result;
                                        $PluginModelTask->together(['result'])->save();
                                    } else {
                                        $PluginModelTask->status = ModelTaskStatus::WAIT['value'];
                                        $PluginModelTask->success_execution_count = Db::raw('success_execution_count + 1');
                                        $PluginModelTask->last_heartbeat = date('Y-m-d H:i:s', strtotime('+10 seconds'));
                                        $data['params']['form_data']['pre_episode'] = $result['content'];
                                        $PluginShortplayDramaActor = PluginShortplayDramaActor::where('drama_id', $PluginShortplayDrama->id)->with('actor')->select();
                                        $actors = [];
                                        foreach ($PluginShortplayDramaActor as $PluginShortplayActor) {
                                            $actors[] = "{$PluginShortplayActor->actor->name}{{$PluginShortplayActor->actor->actor_id}}";
                                        }
                                        $data['params']['form_data']['actors'] = implode(',', $actors);
                                        $PluginModelTask->result->params = $data;
                                        $PluginModelTask->together(['result'])->save();
                                    }
                                    $taksStatus = ModelTaskStatus::SUCCESS['value'];
                                    Db::commit();
                                } catch (\Throwable $th) {
                                    Db::rollback();
                                    Log::error('续写分集失败 Error:' . $th->getMessage() . PHP_EOL . $th->getTraceAsString());
                                }
                            }
                            if ($taksStatus == ModelTaskStatus::FAIL['value']) {
                                $PluginModelTask = PluginModelTask::where(['id' => $PluginModelTask->id])->find();
                                if (
                                    $PluginModelTask->expectation_execution_count === null
                                    || ($PluginModelTask->expectation_execution_count !== null && $PluginModelTask->success_execution_count + 1 >= $PluginModelTask->expectation_execution_count)
                                    || ($PluginModelTask->expectation_execution_count !== null && $PluginModelTask->execution_count >= $PluginModelTask->expectation_execution_count * 2)
                                ) {
                                    $PluginModelTask->status = ModelTaskStatus::FAIL['value'];
                                    $PluginModelTask->save();
                                    Push::send([
                                        'uid' => $data['uid'],
                                        'channels_uid' => $data['channels_uid'],
                                        'event' => 'continueepisode',
                                    ], [
                                        'drama_id' => $data['drama_id']
                                    ]);
                                } else {
                                    $PluginModelTask->status = ModelTaskStatus::WAIT['value'];
                                    $PluginModelTask->last_heartbeat = date('Y-m-d H:i:s', strtotime('+10 seconds'));
                                    $PluginModelTask->save();
                                }
                            } else {
                                Push::send([
                                    'uid' => $data['uid'],
                                    'channels_uid' => $data['channels_uid'],
                                    'event' => 'continueepisode',
                                ], [
                                    'drama_id' => $data['drama_id']
                                ]);
                            }
                            break;
                        case ModelScene::CREATIVE_STORYBOARDS['value']:
                            try {
                                p('start');
                                $result = Yidevs::ChatAssistantCompletions($data['channels_uid'], $data['params']);
                                p($result);
                            } catch (\Throwable $th) {
                                Log::error('CREATIVE_STORYBOARDS Error:' . $th->getMessage() . PHP_EOL . $th->getTraceAsString());
                            }
                            $taksStatus = ModelTaskStatus::FAIL['value'];
                            if (!empty($result)) {
                                Db::startTrans();
                                try {
                                    $previous_dialogues = '';
                                    $PluginShortplayDramaStoryboard = PluginShortplayDramaStoryboard::where(['drama_id' => $data['drama_id'], 'episode_id' => $data['episode_id'], 'sort' => $data['params']['form_data']['storyboard_num']])->order('sort asc')->find();
                                    if (!empty($result['image_prompt'])) {
                                        $PluginShortplayDramaStoryboard->image_prompt = $result['image_prompt'];
                                    }
                                    if (!empty($result['video_prompt'])) {
                                        $PluginShortplayDramaStoryboard->video_prompt = $result['video_prompt'];
                                    }
                                    $PluginShortplayDramaStoryboard->shot_type = $result['shot_type'];
                                    $PluginShortplayDramaStoryboard->shot_angle = $result['shot_angle'];
                                    $PluginShortplayDramaStoryboard->shot_motion = $result['shot_motion'];
                                    if (empty($result['narration'])) {
                                        $PluginShortplayDramaStoryboard->narration = $result['narration'];
                                    }
                                    // $PluginShortplayDramaStoryboard->sfx = $storyboard['sfx'];
                                    $PluginShortplayDramaStoryboard->duration = $result['duration'];
                                    $PluginShortplayDramaStoryboard->save();

                                    PluginShortplayDramaStoryboardActor::where(['storyboard_id' => $PluginShortplayDramaStoryboard->id])->delete();
                                    PluginShortplayDramaStoryboardDialogue::where(['storyboard_id' => $PluginShortplayDramaStoryboard->id])->delete();
                                    PluginShortplayDramaStoryboardProp::where(['storyboard_id' => $PluginShortplayDramaStoryboard->id])->delete();

                                    if (!empty($result['actor'])) {
                                        foreach ($result['actor'] as $actor_key => $actor) {
                                            $PluginShortplayActor = null;
                                            if (!empty($actor['actor_id'])) {
                                                $PluginShortplayActor = PluginShortplayActor::where(['actor_id' => $actor['actor_id']])->find();
                                            } else {
                                                $PluginShortplayActor = PluginShortplayActor::where(['channels_uid' => $data['channels_uid'], 'name' => $actor['name'], 'drama_id' => $PluginShortplayDramaStoryboard->drama_id])->find();
                                            }
                                            if (!$PluginShortplayActor) {
                                                $PluginShortplayActor = new PluginShortplayActor;
                                                $PluginShortplayActor->channels_uid = $data['channels_uid'];
                                                $PluginShortplayActor->uid = $data['uid'];
                                                $PluginShortplayActor->drama_id = $data['drama_id'];
                                                $PluginShortplayActor->episode_id = $data['episode_id'];
                                                $PluginShortplayActor->actor_id = uniqid();
                                                $PluginShortplayActor->name = $actor['name'];
                                                $PluginShortplayActor->remarks = $actor['description'];
                                                $PluginShortplayActor->status = ActorStatus::INITIALIZING['value'];
                                                $PluginShortplayActor->save();
                                                $PluginShortplayDramaActor = new PluginShortplayDramaActor();
                                                $PluginShortplayDramaActor->channels_uid = $data['channels_uid'];
                                                $PluginShortplayDramaActor->drama_id = $data['drama_id'];
                                                $PluginShortplayDramaActor->actor_id = $PluginShortplayActor->id;
                                                $PluginShortplayDramaActor->save();
                                                $PluginShortplayDramaEpisodeActor = new PluginShortplayDramaEpisodeActor;
                                                $PluginShortplayDramaEpisodeActor->channels_uid = $data['channels_uid'];
                                                $PluginShortplayDramaEpisodeActor->drama_id = $data['drama_id'];
                                                $PluginShortplayDramaEpisodeActor->episode_id = $data['episode_id'];
                                                $PluginShortplayDramaEpisodeActor->actor_id = $PluginShortplayActor->id;
                                                $PluginShortplayDramaEpisodeActor->save();
                                            }
                                            $PluginShortplayDramaStoryboardActor = new PluginShortplayDramaStoryboardActor();
                                            $PluginShortplayDramaStoryboardActor->channels_uid = $data['channels_uid'];
                                            $PluginShortplayDramaStoryboardActor->drama_id = $data['drama_id'];
                                            $PluginShortplayDramaStoryboardActor->episode_id = $data['episode_id'];
                                            $PluginShortplayDramaStoryboardActor->storyboard_id = $PluginShortplayDramaStoryboard->id;
                                            $PluginShortplayDramaStoryboardActor->actor_id = $PluginShortplayActor->id;
                                            $PluginShortplayDramaStoryboardActor->save();
                                        }
                                    }
                                    $PluginShortplayActor = null;
                                    if (!empty($result['dialogues'])) {
                                        foreach ($result['dialogues'] as $dialogue_key => $dialogue) {
                                            if (!empty($dialogue['actor']['actor_id'])) {
                                                $PluginShortplayActor = PluginShortplayActor::where(['actor_id' => $dialogue['actor']['actor_id']])->find();
                                            } else {
                                                $PluginShortplayActor = PluginShortplayActor::where(['channels_uid' => $data['channels_uid'], 'name' => $dialogue['actor']['name'], 'drama_id' => $PluginShortplayDramaStoryboard->drama_id])->find();
                                            }
                                            if (!$PluginShortplayActor) {
                                                $PluginShortplayActor = new PluginShortplayActor;
                                                $PluginShortplayActor->channels_uid = $data['channels_uid'];
                                                $PluginShortplayActor->uid = $data['uid'];
                                                $PluginShortplayActor->drama_id = $data['drama_id'];
                                                $PluginShortplayActor->episode_id = $data['episode_id'];
                                                $PluginShortplayActor->actor_id = uniqid();
                                                $PluginShortplayActor->name = $dialogue['actor']['name'];
                                                $PluginShortplayActor->remarks = $dialogue['actor']['description'];
                                                $PluginShortplayActor->status = ActorStatus::INITIALIZING['value'];
                                                $PluginShortplayActor->save();
                                                $PluginShortplayDramaActor = new PluginShortplayDramaActor();
                                                $PluginShortplayDramaActor->channels_uid = $data['channels_uid'];
                                                $PluginShortplayDramaActor->drama_id = $data['drama_id'];
                                                $PluginShortplayDramaActor->actor_id = $PluginShortplayActor->id;
                                                $PluginShortplayDramaActor->save();
                                                $PluginShortplayDramaEpisodeActor = new PluginShortplayDramaEpisodeActor;
                                                $PluginShortplayDramaEpisodeActor->channels_uid = $data['channels_uid'];
                                                $PluginShortplayDramaEpisodeActor->drama_id = $data['drama_id'];
                                                $PluginShortplayDramaEpisodeActor->episode_id = $data['episode_id'];
                                                $PluginShortplayDramaEpisodeActor->actor_id = $PluginShortplayActor->id;
                                                $PluginShortplayDramaEpisodeActor->save();
                                                $PluginShortplayDramaStoryboardActor = new PluginShortplayDramaStoryboardActor();
                                                $PluginShortplayDramaStoryboardActor->channels_uid = $data['channels_uid'];
                                                $PluginShortplayDramaStoryboardActor->drama_id = $data['drama_id'];
                                                $PluginShortplayDramaStoryboardActor->episode_id = $data['episode_id'];
                                                $PluginShortplayDramaStoryboardActor->storyboard_id = $PluginShortplayDramaStoryboard->id;
                                                $PluginShortplayDramaStoryboardActor->actor_id = $PluginShortplayActor->id;
                                                $PluginShortplayDramaStoryboardActor->save();
                                            }
                                            $PluginShortplayDramaStoryboardDialogue = new PluginShortplayDramaStoryboardDialogue();
                                            $PluginShortplayDramaStoryboardDialogue->channels_uid = $data['channels_uid'];
                                            $PluginShortplayDramaStoryboardDialogue->storyboard_id = $PluginShortplayDramaStoryboard->id;
                                            $PluginShortplayDramaStoryboardDialogue->actor_id = $PluginShortplayActor->id;
                                            $PluginShortplayDramaStoryboardDialogue->prosody_speed = $dialogue['prosody_speed'];
                                            $PluginShortplayDramaStoryboardDialogue->prosody_volume = $dialogue['prosody_volume'];
                                            $PluginShortplayDramaStoryboardDialogue->emotion = $dialogue['emotion'];
                                            $PluginShortplayDramaStoryboardDialogue->start_time = $dialogue['start_time'];
                                            $PluginShortplayDramaStoryboardDialogue->end_time = $dialogue['end_time'];
                                            $PluginShortplayDramaStoryboardDialogue->inner_monologue = empty($dialogue['inner_monologue']) ? 0 : 1;
                                            $content = $dialogue['content'];
                                            if (empty($content)) {
                                                $content = '……';
                                            }
                                            $PluginShortplayDramaStoryboardDialogue->content = $content;
                                            $PluginShortplayDramaStoryboardDialogue->save();
                                            $previous_dialogues .= "{$PluginShortplayActor->name}：{$content}\n";
                                        }
                                    }
                                    if (!empty($result['prop'])) {
                                        foreach ($result['prop'] as $prop_key => $prop) {
                                            if (!empty($prop['prop_id'])) {
                                                $PluginShortplayProp = PluginShortplayProp::where(['id' => $prop['prop_id']])->find();
                                            } else {
                                                $PluginShortplayProp = PluginShortplayProp::where(['name' => $prop['name'], 'channels_uid' => $data['channels_uid'], 'drama_id' => $data['drama_id']])->find();
                                            }
                                            if (!$PluginShortplayProp) {
                                                $PluginShortplayProp = new PluginShortplayProp;
                                                $PluginShortplayProp->channels_uid = $data['channels_uid'];
                                                $PluginShortplayProp->uid = $data['uid'];
                                                $PluginShortplayProp->drama_id = $data['drama_id'];
                                                $PluginShortplayProp->episode_id = $data['episode_id'];
                                                $PluginShortplayProp->prop_id = uniqid();
                                                $PluginShortplayProp->name = $prop['name'];
                                                $PluginShortplayProp->description = $prop['description'];
                                                $PluginShortplayProp->status = PropStatus::INITIALIZING['value'];
                                                $PluginShortplayProp->save();
                                            }
                                            $PluginShortplayDramaStoryboardProp = new PluginShortplayDramaStoryboardProp();
                                            $PluginShortplayDramaStoryboardProp->channels_uid = $data['channels_uid'];
                                            $PluginShortplayDramaStoryboardProp->storyboard_id = $PluginShortplayDramaStoryboard->id;
                                            $PluginShortplayDramaStoryboardProp->prop_id = $PluginShortplayProp->id;
                                            $PluginShortplayDramaStoryboardProp->save();
                                        }
                                    }
                                    $PluginModelTask = PluginModelTask::where(['id' => $PluginModelTask->id])->with(['result'])->find();
                                    if (
                                        $PluginModelTask->expectation_execution_count === null
                                        || ($PluginModelTask->expectation_execution_count !== null && $PluginModelTask->success_execution_count + 1 >= $PluginModelTask->expectation_execution_count)
                                    ) {
                                        $PluginModelTask->status = ModelTaskStatus::SUCCESS['value'];
                                        $PluginModelTask->success_execution_count = Db::raw('success_execution_count + 1');
                                        $PluginModelTask->result->result = $result;
                                        $PluginModelTask->together(['result'])->save();
                                    } else {
                                        $PluginModelTask->status = ModelTaskStatus::WAIT['value'];
                                        $PluginModelTask->success_execution_count = Db::raw('success_execution_count + 1');
                                        $PluginModelTask->last_heartbeat = date('Y-m-d H:i:s', strtotime('+10 seconds'));
                                        $data['params']['form_data']['previous_description'] = $PluginShortplayDramaStoryboard->description;
                                        $data['params']['form_data']['storyboard_num'] = $data['params']['form_data']['storyboard_num'] + 1;
                                        $PluginShortplayDramaStoryboard = PluginShortplayDramaStoryboard::where(['drama_id' => $data['drama_id'], 'episode_id' => $data['episode_id'], 'sort' => $data['params']['form_data']['storyboard_num']])->order('sort asc')->find();
                                        if ($PluginShortplayDramaStoryboard) {
                                            $data['params']['form_data']['description'] = $PluginShortplayDramaStoryboard->description;
                                        } else {
                                            $PluginModelTask->status = ModelTaskStatus::SUCCESS['value'];
                                        }
                                        $NextPluginShortplayDramaStoryboard = PluginShortplayDramaStoryboard::where(['drama_id' => $PluginShortplayDramaStoryboard->drama_id, 'episode_id' => $PluginShortplayDramaStoryboard->episode_id, 'sort' => $PluginShortplayDramaStoryboard->sort + 1])->order('sort asc')->find();
                                        if ($NextPluginShortplayDramaStoryboard) {
                                            $next_description = $NextPluginShortplayDramaStoryboard->description;
                                        } else {
                                            $next_description = '';
                                        }
                                        $data['params']['form_data']['next_description'] = $next_description;
                                        $data['params']['form_data']['previous_dialogues'] = $previous_dialogues;
                                        $PluginModelTask->result->params = $data;
                                        $PluginModelTask->together(['result'])->save();
                                    }
                                    $taksStatus = ModelTaskStatus::SUCCESS['value'];
                                    Db::commit();
                                } catch (\Throwable $th) {
                                    Db::rollback();
                                    Log::error('Generate Storyboard Error:' . $th->getMessage() . PHP_EOL . $th->getTraceAsString());
                                }
                            }
                            if ($taksStatus == ModelTaskStatus::FAIL['value']) {
                                $PluginModelTask = PluginModelTask::where(['id' => $PluginModelTask->id])->find();
                                if (
                                    $PluginModelTask->expectation_execution_count === null
                                    || ($PluginModelTask->expectation_execution_count !== null && $PluginModelTask->success_execution_count + 1 >= $PluginModelTask->expectation_execution_count)
                                    || ($PluginModelTask->expectation_execution_count !== null && $PluginModelTask->execution_count >= $PluginModelTask->expectation_execution_count * 2)
                                ) {
                                    $PluginModelTask->status = ModelTaskStatus::FAIL['value'];
                                    $PluginModelTask->save();
                                    Push::send([
                                        'uid' => $data['uid'],
                                        'channels_uid' => $data['channels_uid'],
                                        'hash' => $data['drama_id'],
                                        'event' => 'generatescenestoryboard',
                                    ], [
                                        'drama_id' => $data['drama_id'],
                                        'episode_id' => $data['episode_id'],
                                    ]);
                                } else {
                                    $PluginModelTask->status = ModelTaskStatus::WAIT['value'];
                                    $PluginModelTask->last_heartbeat = date('Y-m-d H:i:s', strtotime('+10 seconds'));
                                    $PluginModelTask->save();
                                }
                            } else {
                                Push::send([
                                    'uid' => $data['uid'],
                                    'channels_uid' => $data['channels_uid'],
                                    'hash' => $data['drama_id'],
                                    'event' => 'generatescenestoryboard',
                                ], [
                                    'drama_id' => $data['drama_id'],
                                    'episode_id' => $data['episode_id'],
                                ]);
                            }
                            break;
                        default:
                            break;
                    }
                });
            } catch (\Throwable $th) {
                Log::error("Chat Submit Process Error:" . $th->getMessage(), $th->getTrace());
            }
        });
    }
}
