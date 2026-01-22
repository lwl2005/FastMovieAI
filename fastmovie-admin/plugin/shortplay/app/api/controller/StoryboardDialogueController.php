<?php

namespace plugin\shortplay\app\api\controller;

use app\Basic;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;
use plugin\shortplay\app\model\PluginShortplayActor;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\shortplay\app\model\PluginShortplayDramaActor;
use plugin\shortplay\app\model\PluginShortplayDramaEpisode;
use plugin\shortplay\app\model\PluginShortplayDramaEpisodeActor;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboard;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboardActor;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboardDialogue;
use support\Request;
use support\think\Db;

class StoryboardDialogueController extends Basic
{
    public function index(Request $request)
    {
        $drama_id = $request->get('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $episode_id = $request->get('episode_id');
        if ($episode_id) {
            $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['id' => $episode_id, 'drama_id' => $PluginShortplayDrama->id])->find();
            if (!$PluginShortplayDramaEpisode) {
                return $this->fail('分集不存在');
            }
        }
        $where = [];
        $storyboard_id = $request->get('storyboard_id');
        if ($storyboard_id) {
            $PluginShortplayDramaStoryboard = PluginShortplayDramaStoryboard::where(['id' => $storyboard_id, 'drama_id' => $drama_id])->find();
            if (!$PluginShortplayDramaStoryboard) {
                return $this->fail('分镜不存在');
            }
            $where[] = ['storyboard_id', '=', $PluginShortplayDramaStoryboard->id];
        } else {
            $ids = PluginShortplayDramaStoryboard::where(['episode_id' => $PluginShortplayDramaEpisode->id, 'drama_id' => $drama_id])->column('id');
            if (empty($ids)) {
                return $this->resData([]);
            }
            $where[] = ['storyboard_id', 'in', $ids];
        }
        $PluginShortplayDramaStoryboardDialogue = PluginShortplayDramaStoryboardDialogue::where($where)->with(['actor', 'storyboard'])->order('start_time asc,id asc')->select()->each(function ($item) {
            $item->voice_level = 'dialogue';
            if (!$item->voice) {
                $ActorVoiceLevel = [
                    [
                        'level' => 'storyboard',
                        'model' => PluginShortplayDramaStoryboardActor::class,
                        'where' => [
                            'storyboard_id' => $item->storyboard_id,
                            'actor_id' => $item->actor_id
                        ]
                    ],
                    [
                        'level' => 'episode',
                        'model' => PluginShortplayDramaEpisodeActor::class,
                        'where' => [
                            'episode_id' => $item->storyboard->episode_id,
                            'actor_id' => $item->actor_id
                        ]
                    ],
                    [
                        'level' => 'drama',
                        'model' => PluginShortplayDramaActor::class,
                        'where' => [
                            'drama_id' => $item->storyboard->drama_id,
                            'actor_id' => $item->actor_id
                        ]
                    ],
                    [
                        'level' => 'actor',
                        'model' => PluginShortplayActor::class,
                        'where' => [
                            'actor_id' => $item->actor_id
                        ]
                    ]
                ];
                foreach ($ActorVoiceLevel as $ActorVoiceLevelItem) {
                    $PluginActorVoice = $ActorVoiceLevelItem['model']::where($ActorVoiceLevelItem['where'])->find();
                    if ($PluginActorVoice && $PluginActorVoice->voice) {
                        $item->voice = $PluginActorVoice->voice;
                        $item->voice_level = $ActorVoiceLevelItem['level'];
                        break;
                    }
                }
            }
        });
        return $this->resData($PluginShortplayDramaStoryboardDialogue);
    }
    public function delete(Request $request)
    {
        $drama_id = $request->post('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $storyboard_id = $request->post('storyboard_id');
        $PluginShortplayDramaStoryboard = PluginShortplayDramaStoryboard::where(['id' => $storyboard_id, 'drama_id' => $drama_id])->find();
        if (!$PluginShortplayDramaStoryboard) {
            return $this->fail('分镜不存在');
        }
        $dialogue_id = $request->post('dialogue_id');
        $PluginShortplayDramaStoryboardDialogue = PluginShortplayDramaStoryboardDialogue::where(['id' => $dialogue_id, 'storyboard_id' => $PluginShortplayDramaStoryboard->id])->find();
        if (!$PluginShortplayDramaStoryboardDialogue) {
            return $this->success('已删除');
        }
        if ($PluginShortplayDramaStoryboardDialogue->delete()) {
            return $this->success('删除成功');
        } else {
            return $this->fail('删除失败');
        }
    }
    public function save(Request $request)
    {
        $drama_id = $request->post('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $storyboard_id = $request->post('storyboard_id');
        $PluginShortplayDramaStoryboard = PluginShortplayDramaStoryboard::where(['id' => $storyboard_id, 'drama_id' => $drama_id])->find();
        if (!$PluginShortplayDramaStoryboard) {
            return $this->fail('分镜不存在');
        }
        $actor_id = $request->post('actor_id');
        $PluginShortplayActor = PluginShortplayActor::where(['id' => $actor_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayActor) {
            $PluginShortplayActor = PluginShortplayActor::where(['id' => $actor_id, 'channels_uid' => $request->channels_uid])->whereNull('uid')->find();
        }
        if (!$PluginShortplayActor) {
            return $this->fail('演员不存在');
        }
        $id = $request->post('id');
        if ($id) {
            $PluginShortplayDramaStoryboardDialogue = PluginShortplayDramaStoryboardDialogue::where(['id' => $id, 'storyboard_id' => $PluginShortplayDramaStoryboard->id])->find();
            if (!$PluginShortplayDramaStoryboardDialogue) {
                return $this->fail('对话不存在');
            }
        } else {
            $PluginShortplayDramaStoryboardDialogue = new PluginShortplayDramaStoryboardDialogue();
            $PluginShortplayDramaStoryboardDialogue->channels_uid    = $request->channels_uid;
            $PluginShortplayDramaStoryboardDialogue->storyboard_id = $PluginShortplayDramaStoryboard->id;
            $PluginShortplayDramaStoryboardDialogue->actor_id = $PluginShortplayActor->id;
        }
        Db::startTrans();
        try {
            $D = $request->post();
            $PluginShortplayDramaStoryboardDialogue->prosody_speed = $D['prosody_speed'];
            $PluginShortplayDramaStoryboardDialogue->prosody_volume = $D['prosody_volume'];
            $PluginShortplayDramaStoryboardDialogue->emotion = $D['emotion'];
            $PluginShortplayDramaStoryboardDialogue->start_time = $D['start_time'];
            $PluginShortplayDramaStoryboardDialogue->end_time = $D['end_time'];
            $PluginShortplayDramaStoryboardDialogue->content = $D['content'];
            $PluginShortplayDramaStoryboardDialogue->save();
            if (!PluginShortplayDramaEpisodeActor::where(['episode_id' => $PluginShortplayDramaStoryboard->episode_id, 'actor_id' => $actor_id])->count()) {
                $PluginShortplayDramaEpisodeActor = new PluginShortplayDramaEpisodeActor();
                $PluginShortplayDramaEpisodeActor->channels_uid = $request->channels_uid;
                $PluginShortplayDramaEpisodeActor->drama_id = $PluginShortplayDramaStoryboard->drama_id;
                $PluginShortplayDramaEpisodeActor->episode_id = $PluginShortplayDramaStoryboard->episode_id;
                $PluginShortplayDramaEpisodeActor->actor_id = $actor_id;
                $PluginShortplayDramaEpisodeActor->save();
            }
            if (!PluginShortplayDramaActor::where(['drama_id' => $PluginShortplayDrama->id, 'actor_id' => $actor_id])->count()) {
                $PluginShortplayDramaActor = new PluginShortplayDramaActor();
                $PluginShortplayDramaActor->channels_uid = $request->channels_uid;
                $PluginShortplayDramaActor->drama_id = $drama_id;
                $PluginShortplayDramaActor->actor_id = $actor_id;
                $PluginShortplayDramaActor->save();
            }
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->fail($th->getMessage());
        }
        $PluginShortplayDramaStoryboardDialogue = PluginShortplayDramaStoryboardDialogue::where(['id' => $PluginShortplayDramaStoryboardDialogue->id])->with(['actor'])->find();
        return $this->resData($PluginShortplayDramaStoryboardDialogue);
    }
}
