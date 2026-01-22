<?php

namespace plugin\shortplay\app\api\controller;

use app\Basic;
use plugin\shortplay\app\model\PluginShortplayActorCharacterLook;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\shortplay\app\model\PluginShortplayDramaEpisode;
use plugin\shortplay\app\model\PluginShortplayDramaEpisodeActor;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboard;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboardActor;
use plugin\shortplay\app\model\PluginShortplayShare;
use plugin\shortplay\app\model\PluginShortplayShareEpisode;
use support\Request;
use support\think\Db;

class DramaEpisodeController extends Basic
{
    public function details(Request $request)
    {
        $drama_id = $request->get('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $episode_id = $request->get('episode_id');
        $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['id' => $episode_id, 'drama_id' => $PluginShortplayDrama->id])->with('drama')->find();
        if (!$PluginShortplayDramaEpisode) {
            return $this->fail('分集不存在');
        }
        $PluginShortplayDramaEpisode->is_share = PluginShortplayShareEpisode::where(['drama_id' => $PluginShortplayDrama->id, 'episode_id' => $episode_id])->count();
        return $this->resData($PluginShortplayDramaEpisode);
    }
    public function share(Request $request)
    {
        $drama_id = $request->post('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $episode_id = $request->post('episode_id');
        $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['id' => $episode_id, 'drama_id' => $PluginShortplayDrama->id])->find();
        if (!$PluginShortplayDramaEpisode) {
            return $this->fail('分集不存在');
        }
        Db::startTrans();
        try {
            $PluginShortplayShare = PluginShortplayShare::where(['drama_id' => $PluginShortplayDrama->id, 'uid' => $request->uid])->find();
            if (!$PluginShortplayShare) {
                $PluginShortplayShare = new PluginShortplayShare();
                $PluginShortplayShare->drama_id = $PluginShortplayDrama->id;
                $PluginShortplayShare->uid = $request->uid;
                $PluginShortplayShare->channels_uid = $request->channels_uid;
                $PluginShortplayShare->save();
            }
            $PluginShortplayShareEpisode = PluginShortplayShareEpisode::where(['drama_id' => $PluginShortplayDrama->id, 'episode_id' => $PluginShortplayDramaEpisode->id])->find();
            if (!$PluginShortplayShareEpisode) {
                $PluginShortplayShareEpisode = new PluginShortplayShareEpisode();
                $PluginShortplayShareEpisode->share_id = $PluginShortplayShare->id;
                $PluginShortplayShareEpisode->drama_id = $PluginShortplayDrama->id;
                $PluginShortplayShareEpisode->episode_id = $PluginShortplayDramaEpisode->id;
                $PluginShortplayShareEpisode->channels_uid = $request->channels_uid;
                $PluginShortplayShareEpisode->save();
            }
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->fail($th->getMessage());
        }
        return $this->success('分享成功');
    }
    public function episodes(Request $request)
    {
        $drama_id = $request->get('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['drama_id' => $PluginShortplayDrama->id])->with(['scenes' => function ($query) {
            $query->order('sort', 'asc');
        }, 'storyboards' => function ($query) {
            $query->order('sort', 'asc');
        }])->select();
        if (!$PluginShortplayDramaEpisode) {
            return $this->fail('分集不存在');
        }
        return $this->resData($PluginShortplayDramaEpisode);
    }
    public function create(Request $request)
    {
        $drama_id = $request->post('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        Db::startTrans();
        try {
            $id = $request->post('id');
            if ($id) {
                $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['id' => $id, 'drama_id' => $PluginShortplayDrama->id])->find();
                if (!$PluginShortplayDramaEpisode) {
                    throw new \Exception('分集不存在');
                }
                $msg = '更新成功';
            } else {
                $PluginShortplayDramaEpisode = new PluginShortplayDramaEpisode();
                $PluginShortplayDramaEpisode->channels_uid = $request->channels_uid;
                $PluginShortplayDramaEpisode->uid = $request->uid;
                $PluginShortplayDramaEpisode->drama_id = $PluginShortplayDrama->id;
                $PluginShortplayDramaEpisode->episode_no = PluginShortplayDramaEpisode::where(['drama_id' => $PluginShortplayDrama->id])->max('episode_no') + 1;
                $msg = '创建成功';
                $PluginShortplayDrama->episode_num = Db::raw('episode_num + 1');
                $PluginShortplayDrama->save();
            }
            $PluginShortplayDramaEpisode->title = $request->post('title');
            $PluginShortplayDramaEpisode->content = $request->post('content');
            $PluginShortplayDramaEpisode->save();
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->fail($th->getMessage());
        }
        return $this->success($msg);
    }
    public function delete(Request $request)
    {
        $id = $request->post('id');
        $drama_id = $request->post('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['id' => $id, 'drama_id' => $PluginShortplayDrama->id])->find();
        if (!$PluginShortplayDramaEpisode) {
            return $this->fail('分集不存在');
        }
        Db::startTrans();
        try {
            $PluginShortplayDramaEpisode->delete();
            # 重排分集
            $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['drama_id' => $drama_id])->order('episode_no asc')->select();
            $episode_no = 1;
            foreach ($PluginShortplayDramaEpisode as $PluginShortplayDramaEpisode) {
                $PluginShortplayDramaEpisode->episode_no = $episode_no;
                $PluginShortplayDramaEpisode->save();
                $episode_no++;
            }
            $PluginShortplayDrama->episode_num = Db::raw('episode_num - 1');
            $PluginShortplayDrama->save();
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->fail($th->getMessage());
        }
        return $this->success('删除成功');
    }
    public function deleteActor(Request $request)
    {
        $id = $request->post('id');
        $drama_id = $request->post('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $episode_id = $request->post('episode_id');
        $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['id' => $episode_id, 'drama_id' => $PluginShortplayDrama->id])->find();
        if (!$PluginShortplayDramaEpisode) {
            return $this->fail('分集不存在');
        }
        $PluginShortplayDramaEpisodeActor = PluginShortplayDramaEpisodeActor::where(['actor_id' => $id, 'episode_id' => $PluginShortplayDramaEpisode->id])->find();
        if (!$PluginShortplayDramaEpisodeActor) {
            return $this->fail('演员不存在');
        }
        Db::startTrans();
        try {
            $PluginShortplayDramaEpisodeActor->delete();
            PluginShortplayDramaStoryboardActor::where(['episode_id' => $PluginShortplayDramaEpisode->id, 'actor_id' => $PluginShortplayDramaEpisodeActor->actor_id])->delete();
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->fail($th->getMessage());
        }
        return $this->success('删除成功');
    }
    public function CharacterLook(Request $request)
    {
        $actor_id = $request->post('actor_id');
        $drama_id = $request->post('drama_id');
        $episode_id = $request->post('episode_id');
        $character_look_id = $request->post('character_look_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['id' => $episode_id, 'drama_id' => $PluginShortplayDrama->id])->find();
        if (!$PluginShortplayDramaEpisode) {
            return $this->fail('分集不存在');
        }
        $PluginShortplayDramaEpisodeActor = PluginShortplayDramaEpisodeActor::where(['episode_id' => $PluginShortplayDramaEpisode->id, 'actor_id' => $actor_id])->find();
        if (!$PluginShortplayDramaEpisodeActor) {
            return $this->fail('演员不存在');
        }
        $PluginShortplayActorCharacterLook = PluginShortplayActorCharacterLook::where(['id' => $character_look_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayActorCharacterLook) {
            return $this->fail('演员妆容不存在');
        }
        $PluginShortplayDramaEpisodeActor->character_look_id = $PluginShortplayActorCharacterLook->id;
        $PluginShortplayDramaEpisodeActor->headimg = $PluginShortplayActorCharacterLook->headimg;
        $PluginShortplayDramaEpisodeActor->three_view_image = $PluginShortplayActorCharacterLook->three_view_image;
        $PluginShortplayDramaEpisodeActor->save();
        return $this->success('设置成功');
    }
}
