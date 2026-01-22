<?php

namespace plugin\shortplay\app\model;

use plugin\control\expose\helper\Uploads;
use app\model\Basic;
use plugin\control\app\model\PluginChannelsUser;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;
use plugin\shortplay\utils\enum\ActorStatus;
use plugin\user\app\model\PluginUser;
use support\Request;
use think\facade\Db;

class PluginShortplayActor extends Basic
{
    protected function getOptions(): array
    {
        return [
            'type' => [
                // 设置JSON字段的类型
                'voice'    =>    'json',
            ]
        ];
    }
    public function getThreeViewImageAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setThreeViewImageAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function getHeadimgAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setHeadimgAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function getOriginHeadimgAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $data['origin_headimg']);
    }
    public function getOriginThreeViewImageAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $data['origin_three_view_image']);
    }
    public function user()
    {
        return $this->hasOne(PluginUser::class, 'id', 'uid');
    }

    public function channels()
    {
        return $this->hasOne(PluginChannelsUser::class, 'id', 'channels_uid');
    }
    public static function onAfterRead($model)
    {
        $model->status_enum = ActorStatus::get($model->status);
        if ($model->status != ActorStatus::GENERATED['value']) {
            $imageState = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::ACTOR_IMAGE['value']]);
            $threeViewImageState = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::ACTOR_THREE_VIEW_IMAGE['value']]);
            if ($imageState > 0 || $threeViewImageState > 0) {
                $model->status_enum = ActorStatus::PENDING;
            }
        }
    }

    /**
     * 统一查询构造器
     * @param array $params 包含 drama_id, episode_id, storyboard_id 的数组
     * @return PluginShortplayActor
     */
    public static function actorQuery($params = [])
    {
        $query = (new PluginShortplayActor)->alias('actor');
        // 处理参数，统一为 0 或具体 ID
        $sid = !empty($params['storyboard_id']) ? (int)$params['storyboard_id'] : 0;
        $eid = !empty($params['episode_id']) ? (int)$params['episode_id'] : 0;
        $did = !empty($params['drama_id']) ? (int)$params['drama_id'] : 0;

        $fields = ['actor.*,actor.headimg as origin_headimg,actor.three_view_image as origin_three_view_image'];
        $activeLevels = [];

        /**
         * 1. 动态关联所有可能的层级 (全部使用 LEFT JOIN 保证回退链路通畅)
         */
        if ($sid) {
            $query->leftJoin('php_plugin_shortplay_drama_storyboard_actor s_actor', "s_actor.actor_id = actor.id AND s_actor.storyboard_id = $sid");
            $activeLevels['s'] = 's_actor';
            $fields[] = 's_actor.id as storyboard_actor_id';
        }
        if ($eid) {
            $query->leftJoin('php_plugin_shortplay_drama_episode_actor e_actor', "e_actor.actor_id = actor.id AND e_actor.episode_id = $eid");
            $activeLevels['e'] = 'e_actor';
            $fields[] = 'e_actor.id as episode_actor_id';
        }
        if ($did) {
            $query->leftJoin('php_plugin_shortplay_drama_actor d_actor', "d_actor.actor_id = actor.id AND d_actor.drama_id = $did");
            $activeLevels['d'] = 'd_actor';
            $fields[] = 'd_actor.id as drama_actor_id';
        }

        /**
         * 2. 强制过滤：确保角色必须存在于你传入的最深层 ID 中
         * 对应你说的：“必须在这一集中出现的角色才显示”
         */
        if ($sid) {
            $query->whereNotNull('s_actor.id'); // 必须在分镜表有记录
            $fields[] = 's_actor.character_look_id';
        } elseif ($eid) {
            $query->whereNotNull('e_actor.id'); // 必须在分集表有记录
            $fields[] = 'e_actor.character_look_id';
        } elseif ($did) {
            $query->whereNotNull('d_actor.id'); // 必须在短剧表有记录
            $fields[] = 'd_actor.character_look_id';
        }
        if ($sid || $eid || $did) {
            /**
             * 3. 字段组独立判定 (Image Group & Voice Group)
             */
            $groupConfigs = [
                'img'   => ['check' => 'headimg', 'fields' => ['headimg', 'three_view_image']],
                'voice' => ['check' => 'voice', 'fields' => ['voice']],
            ];

            foreach ($groupConfigs as $config) {
                $c = $config['check'];

                // 动态构建层级判定逻辑 (按优先级顺序遍历 activeLevels)
                $winnerSql = "CASE ";
                foreach ($activeLevels as $key => $alias) {
                    $winnerSql .= "WHEN $alias.$c IS NOT NULL AND $alias.$c != '' THEN '$key' ";
                }
                $winnerSql .= "ELSE 'b' END";

                // 应用到该组所有字段
                foreach ($config['fields'] as $f) {
                    $caseSql = "CASE ($winnerSql) ";
                    foreach ($activeLevels as $key => $alias) {
                        $caseSql .= "WHEN '$key' THEN $alias.$f ";
                    }
                    $caseSql .= "ELSE actor.$f END AS $f";
                    $fields[] = \think\facade\Db::raw($caseSql);
                }
            }
        }
        $query->field($fields);
        return $query;
    }
}
