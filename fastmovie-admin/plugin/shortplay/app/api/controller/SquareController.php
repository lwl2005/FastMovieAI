<?php

namespace plugin\shortplay\app\api\controller;

use app\Basic;
use plugin\shortplay\app\model\PluginShortplayDramaEpisode;
use plugin\shortplay\app\model\PluginShortplayShare;
use plugin\shortplay\app\model\PluginShortplayShareEpisode;
use plugin\shortplay\app\model\PluginShortplayShareLikes;
use plugin\user\app\model\PluginUser;
use support\Request;
use think\facade\Db;

class SquareController extends Basic
{
    protected $notNeedLogin = ['index', 'details'];
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $uid = $request->uid;
        $PluginShortplayShare = PluginShortplayShare::with(['drama' => function ($query) {
            $query->field('id,channels_uid,title,cover');
        }, 'user' => function ($query) {
            $query->field('id,channels_uid,nickname,headimg');
        }])->order('update_time desc,id desc')->paginate($limit)->each(function ($item) use ($uid) {
            $item->is_likes = false;
            if ($uid) {
                $item->is_likes = PluginShortplayShareLikes::where(['share_id' => $item->id, 'uid' => $uid])->count() > 0;
            }
            $item->episode = PluginShortplayShareEpisode::where(['share_id' => $item->id])->find();
            $item->episode_num = PluginShortplayShareEpisode::where(['share_id' => $item->id])->count();
        });
        if ($PluginShortplayShare->isEmpty()) {
            return $this->fail('暂无数据');
        }
        return $this->resData($PluginShortplayShare);
    }
    public function details(Request $request)
    {
        $drama_id = $request->get('drama_id');
        $episode_id = $request->get('episode_id');
        $PluginShortplayShare = PluginShortplayShare::where(['drama_id' => $drama_id])->find();
        if (!$PluginShortplayShare) {
            return $this->fail('分享不存在');
        }
        $PluginShortplayShareEpisode = PluginShortplayShareEpisode::where(['share_id' => $PluginShortplayShare->id, 'episode_id' => $episode_id])->find();
        if (!$PluginShortplayShareEpisode) {
            return $this->fail('分享分集不存在');
        }
        $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['id' => $PluginShortplayShareEpisode->episode_id, 'drama_id' => $PluginShortplayShare->drama_id])->with(['drama' => function ($query) {
            $query->field('id,channels_uid,title,cover,description');
        }])->find();
        if (!$PluginShortplayDramaEpisode) {
            return $this->fail('分集不存在');
        }
        $PluginShortplayDramaEpisode->user = PluginUser::where(['id' => $PluginShortplayShare->uid])->field('channels_uid,headimg,nickname')->find();
        $PluginShortplayDramaEpisode->share = $PluginShortplayShare;
        $PluginShortplayDramaEpisode->is_likes = false;
        if ($request->uid) {
            $PluginShortplayDramaEpisode->is_likes = PluginShortplayShareLikes::where(['share_id' => $PluginShortplayShare->id, 'uid' => $request->uid])->count() > 0;
        }
        return $this->resData($PluginShortplayDramaEpisode);
    }
    public function episodes(Request $request)
    {
        $drama_id = $request->get('drama_id');
        $PluginShortplayShare = PluginShortplayShare::where(['drama_id' => $drama_id])->find();
        if (!$PluginShortplayShare) {
            return $this->fail('分享不存在');
        }
        $PluginShortplayShareEpisode = PluginShortplayShareEpisode::alias('a')->where(['a.share_id' => $PluginShortplayShare->id])
            ->join('PluginShortplayDramaEpisode e', 'e.id=a.episode_id')
            ->field('a.*,e.episode_no')
            ->order('e.episode_no asc')
            ->select();
        return $this->resData($PluginShortplayShareEpisode);
    }
    public function likes(Request $request)
    {
        $drama_id = $request->post('drama_id');
        $PluginShortplayShare = PluginShortplayShare::where(['drama_id' => $drama_id])->find();
        if (!$PluginShortplayShare) {
            return $this->fail('分享不存在');
        }
        Db::startTrans();
        try {
            $PluginShortplayShareLikes = PluginShortplayShareLikes::where(['share_id' => $PluginShortplayShare->id, 'uid' => $request->uid])->find();
            if ($PluginShortplayShareLikes) {
                $PluginShortplayShare->likes = Db::raw('likes-1');
                $PluginShortplayShare->save();
                $PluginShortplayShareLikes->delete();
            } else {
                $PluginShortplayShare->likes = Db::raw('likes+1');
                $PluginShortplayShare->save();
                $PluginShortplayShareLikes = new PluginShortplayShareLikes();
                $PluginShortplayShareLikes->share_id = $PluginShortplayShare->id;
                $PluginShortplayShareLikes->uid = $request->uid;
                $PluginShortplayShareLikes->save();
            }
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->fail($th->getMessage());
        }
        $PluginShortplayShare = PluginShortplayShare::where(['id' => $PluginShortplayShare->id])->find();
        return $this->resData($PluginShortplayShare);
    }
}
