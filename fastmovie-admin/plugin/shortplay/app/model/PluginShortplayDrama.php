<?php

namespace plugin\shortplay\app\model;

use plugin\control\expose\helper\Uploads;
use app\model\Basic;
use plugin\control\app\model\PluginChannelsUser;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;

class PluginShortplayDrama extends Basic
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
    public function style()
    {
        return $this->hasOne(PluginShortplayStyle::class, 'id', 'style_id');
    }
    public function episodes()
    {
        return $this->hasMany(PluginShortplayDramaEpisode::class, 'drama_id', 'id');
    }
    public function getCoverAttr($value,$data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setCoverAttr($value,$data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function channels()
    {
        return $this->hasOne(PluginChannelsUser::class, 'id', 'channels_uid');
    }
    public static function onAfterRead($model)
    {
        $model->cover_state = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::DRAMA_COVER['value']]);
        $model->continue_episode_state = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::CREATIVE_EPISODE['value']]);
    }
}
