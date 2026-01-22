<?php

namespace plugin\shortplay\app\api\controller;

use app\Basic;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\shortplay\app\model\PluginShortplayDramaScene;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboard;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboardActor;
use plugin\shortplay\app\model\PluginShortplayDramaStoryboardProp;
use support\Request;
use support\think\Db;

class SceneController extends Basic
{
    public function index(Request $request)
    {
        $drama_id = $request->get('drama_id');
        $episode_id = $request->get('episode_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $PluginShortplayDramaScenes = PluginShortplayDramaScene::where(['drama_id' => $PluginShortplayDrama->id, 'episode_id' => $episode_id])->order('sort asc')->select();
        return $this->resData($PluginShortplayDramaScenes);
    }
    public function updateSort(Request $request)
    {
        $drama_id = $request->post('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $scenes = $request->post('scenes');
        if (empty($scenes)) {
            $id = $request->post('id');
            $sort = $request->post('sort');
            $PluginShortplayDramaScene = PluginShortplayDramaScene::where(['drama_id' => $PluginShortplayDrama->id, 'id' => $id])->find();
            if (!$PluginShortplayDramaScene) {
                return $this->fail('场景不存在');
            }
            $PluginShortplayDramaScene->sort = $sort;
            $PluginShortplayDramaScene->save();
        } else {
            Db::startTrans();
            try {
                foreach ($scenes as $scene) {
                    $PluginShortplayDramaScene = PluginShortplayDramaScene::where(['drama_id' => $PluginShortplayDrama->id, 'id' => $scene['id']])->find();
                    if (!$PluginShortplayDramaScene) {
                        continue;
                    }
                    $PluginShortplayDramaScene->sort = $scene['sort'];
                    $PluginShortplayDramaScene->save();
                }
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                return $this->fail($th->getMessage());
            }
        }
        return $this->success('更新成功');
    }
    public function copyScene(Request $request)
    {
        $drama_id = $request->post('drama_id');
        $episode_id = $request->post('episode_id');
        $scene_id = $request->post('scene_id');

        // 校验短剧
        $PluginShortplayDrama = PluginShortplayDrama::where([
            'id' => $drama_id,
            'uid' => $request->uid
        ])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }

        // 校验原场景
        $origin = PluginShortplayDramaScene::where([
            'drama_id' => $drama_id,
            'id' => $scene_id
        ])->find();
        if (!$origin) {
            return $this->fail('原场景不存在');
        }

        Db::startTrans();
        try {
            // 3. 插入复制后的场景
            unset($origin['id']); // 删除原场景ID
            $origin['sort'] = PluginShortplayDramaScene::where(['drama_id' => $drama_id, 'episode_id' => $episode_id])->max('sort') + 1 ?? 1;
            $origin['create_time'] = time();
            $origin['update_time'] = time();
            $origin['episode_id'] = $episode_id;
            $PluginShortplayDramaScene = new PluginShortplayDramaScene;
            $PluginShortplayDramaScene->save($origin);

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->fail($th->getMessage());
        }

        return $this->resData($PluginShortplayDramaScene);
    }
    public function deleteScene(Request $request)
    {
        $drama_id = $request->post('drama_id');
        $episode_id = $request->post('episode_id');
        $id = $request->post('id');

        // 校验短剧是否存在
        $PluginShortplayDrama = PluginShortplayDrama::where([
            'id' => $drama_id,
            'uid' => $request->uid
        ])->find();

        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        if ($id) {
            // 校验场景是否存在
            $PluginShortplayDramaScene = PluginShortplayDramaScene::where([
                'id'      => $id,
                'drama_id' => $drama_id,
                'episode_id' => $episode_id
            ])->find();

            if (!$PluginShortplayDramaScene) {
                return $this->fail('场景不存在');
            }
        }

        Db::startTrans();
        try {
            if ($id) {
                // 1. 删除该场景
                PluginShortplayDramaScene::where([
                    'id' => $id,
                    'drama_id' => $drama_id
                ])->delete();
                PluginShortplayDramaStoryboard::where([
                    'drama_id' => $drama_id,
                    'scene_id' => $PluginShortplayDramaScene->id
                ])->update(['scene_id' => null]);


                // 2. 重新拉取该 episode 的所有场景并重排 sort
                $PluginShortplayDramaScenes = PluginShortplayDramaScene::where([
                    'drama_id' => $drama_id,
                    'episode_id' => $episode_id
                ])
                    ->order('sort asc')
                    ->select()
                    ->toArray();

                $sort = 1;
                foreach ($PluginShortplayDramaScenes as $sb) {
                    PluginShortplayDramaScene::where(['id' => $sb['id']])
                        ->update(['sort' => $sort++]);
                }
            } else {
                $PluginShortplayDramaScenes = PluginShortplayDramaScene::where(['drama_id' => $drama_id, 'episode_id' => $episode_id])->order('sort asc')->select();
                foreach ($PluginShortplayDramaScenes as $scene) {
                    PluginShortplayDramaStoryboard::where(['drama_id' => $drama_id, 'scene_id' => $scene->id])->update(['scene_id' => null]);
                    $scene->delete();
                }
            }

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->fail($th->getMessage());
        }

        return $this->success('删除成功');
    }
    public function insertAfter(Request $request)
    {
        $drama_id = $request->post('drama_id');
        $episode_id = $request->post('episode_id');
        $after_id = $request->post('after_id');

        $drama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$drama) {
            return $this->fail('短剧不存在');
        }

        $afterSort = 0;
        if ($after_id) {
            $after = PluginShortplayDramaScene::where([
                'drama_id' => $drama->id,
                'id' => $after_id,
                'episode_id' => $episode_id
            ])->find();

            if (!$after) {
                return $this->fail('指定的场景不存在');
            }
            $afterSort = $after->sort;
        }

        Db::startTrans();
        try {
            // 从 after.sort + 1 开始，所有后续 +1
            PluginShortplayDramaScene::where([
                'drama_id' => $drama_id,
                'episode_id' => $episode_id
            ])
                ->where('sort', '>', $afterSort)
                ->inc('sort', 1)
                ->update();

            // 插入新的空白场景
            $new = new PluginShortplayDramaScene();
            $new->channels_uid = $request->channels_uid;
            $new->drama_id = $drama->id;
            $new->episode_id = $episode_id;
            $new->sort = $afterSort + 1;
            $new->save();

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->fail($th->getMessage());
        }

        return $this->success('插入成功', ['id' => $new->id]);
    }
    public function update(Request $request)
    {
        $drama_id = $request->post('drama_id');
        $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
        if (!$PluginShortplayDrama) {
            return $this->fail('短剧不存在');
        }
        $episode_id = $request->post('episode_id');
        $id = $request->post('id');
        $D = $request->post();
        if ($id) {
            $PluginShortplayDramaScene = PluginShortplayDramaScene::where(['id' => $id, 'drama_id' => $drama_id, 'episode_id' => $episode_id])->find();
            if (!$PluginShortplayDramaScene) {
                return $this->fail('场景不存在');
            }
        } else {
            $PluginShortplayDramaScene = new PluginShortplayDramaScene();
            $PluginShortplayDramaScene->channels_uid = $request->channels_uid;
            $PluginShortplayDramaScene->uid = $request->uid;
            $PluginShortplayDramaScene->drama_id = $drama_id;
            $PluginShortplayDramaScene->episode_id = $episode_id;
            $PluginShortplayDramaScene->sort = PluginShortplayDramaScene::where(['drama_id' => $drama_id, 'episode_id' => $episode_id])->max('sort') + 1 ?? 1;
        }
        if (!empty($D['title'])) {
            $PluginShortplayDramaScene->title = $D['title'];
        }
        if (!empty($D['scene_space'])) {
            $PluginShortplayDramaScene->scene_space = $D['scene_space'];
        }
        if (!empty($D['scene_location'])) {
            $PluginShortplayDramaScene->scene_location = $D['scene_location'];
        }
        if (!empty($D['scene_time'])) {
            $PluginShortplayDramaScene->scene_time = $D['scene_time'];
        }
        if (!empty($D['scene_weather'])) {
            $PluginShortplayDramaScene->scene_weather = $D['scene_weather'];
        }
        if (!empty($D['description'])) {
            $PluginShortplayDramaScene->description = $D['description'];
        }
        if (!empty($D['atmosphere'])) {
            $PluginShortplayDramaScene->atmosphere = $D['atmosphere'];
        }
        if (!empty($D['image'])) {
            $PluginShortplayDramaScene->image = $D['image'];
        }
        $PluginShortplayDramaScene->save();
        return $this->resData($PluginShortplayDramaScene);
    }
}
