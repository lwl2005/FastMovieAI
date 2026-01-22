<?php

namespace plugin\shortplay\app\api\controller;

use app\Basic;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\shortplay\app\model\PluginShortplayDramaEpisode;
use support\Request;

class WorksController extends Basic
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $where[] = ['uid', '=', $request->uid];
        $name = $request->get('name');
        if ($name) {
            $where[] = ['name', 'like', '%' . $name . '%'];
        }
        $script = $request->get('script', 'all');
        if ($script != 'all') {
            $where[] = ['script', '=', $script];
        }
        $PluginShortplayDrama = PluginShortplayDrama::where($where)->order('id', 'desc')->paginate($limit)->each(function ($item) {
        });
        return $this->resData($PluginShortplayDrama);
    }
    public function updateCover(Request $request)
    {
        $id = $request->post('id');
        $cover = $request->post('cover');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $PluginShortplayDrama->cover = $cover;
        $PluginShortplayDrama->save();
        return $this->success('更新成功');
    }
    public function details(Request $request)
    {
        $id = $request->get('id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $id, 'uid' => $request->uid])->with(['episodes','style'])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        if ($PluginShortplayDrama->cover_state > 0) {
            $PluginShortplayDrama->cover = null;
        }
        return $this->resData($PluginShortplayDrama);
    }
    public function episode(Request $request)
    {
        $drama_id = $request->get('drama_id');
        $episode_id = $request->get('episode_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['drama_id' => $PluginShortplayDrama->id, 'id' => $episode_id])->with(['scenes' => function ($query) {
            $query->order('sort', 'asc');
        }, 'storyboards' => function ($query) {
            $query->order('sort', 'asc');
        }])->find();
        if (!$PluginShortplayDramaEpisode) {
            return $this->fail('分集不存在');
        }
        return $this->resData($PluginShortplayDramaEpisode);
    }
}
