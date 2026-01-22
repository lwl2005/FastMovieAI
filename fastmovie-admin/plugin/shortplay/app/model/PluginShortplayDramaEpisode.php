<?php

namespace plugin\shortplay\app\model;

use plugin\control\expose\helper\Uploads;
use app\model\Basic;
use plugin\control\app\model\PluginChannelsUser;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;

class PluginShortplayDramaEpisode extends Basic
{
    public function scenes()
    {
        return $this->hasMany(PluginShortplayDramaScene::class, 'episode_id', 'id');
    }
    public function storyboards()
    {
        return $this->hasMany(PluginShortplayDramaStoryboard::class, 'episode_id', 'id')->with(['actors', 'sceneFind', 'props', 'dialogues']);
    }
    public function actors()
    {
        return $this->hasMany(PluginShortplayDramaEpisodeActor::class, 'episode_id', 'id')->with('actor');
    }
    public function getCoverAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setCoverAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function getVideoPathAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setVideoPathAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function channels()
    {
        return $this->hasOne(PluginChannelsUser::class, 'id', 'channels_uid');
    }
    public function drama()
    {
        return $this->hasOne(PluginShortplayDrama::class, 'id', 'drama_id');
    }
    public static function onAfterRead($model)
    {
        $model->init_scene_state = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::CREATIVE_SCENES['value']]);
        $model->init_storyboard_state = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::CREATIVE_STORYBOARDS['value']]);
    }
}
