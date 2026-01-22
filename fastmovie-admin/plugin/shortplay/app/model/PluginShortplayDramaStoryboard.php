<?php

namespace plugin\shortplay\app\model;

use app\model\Basic;
use plugin\control\expose\helper\Uploads;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;

class PluginShortplayDramaStoryboard extends Basic
{
    public function actors()
    {
        return $this->hasMany(PluginShortplayDramaStoryboardActor::class, 'storyboard_id', 'id')->with('actor');
    }
    public function sceneFind()
    {
        return $this->hasOne(PluginShortplayDramaScene::class, 'id', 'scene_id');
    }
    public function props()
    {
        return $this->hasMany(PluginShortplayDramaStoryboardProp::class, 'storyboard_id', 'id')->with('prop');
    }
    public function dialogues()
    {
        return $this->hasMany(PluginShortplayDramaStoryboardDialogue::class, 'storyboard_id', 'id')->with('actor');
    }
    public function getImageAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setImageAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function getVideoAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setVideoAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function getNarrationAudioAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setNarrationAudioAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public static function onAfterRead($model)
    {
        $model->image_state = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::STORYBOARD_IMAGE['value']]);
        $model->video_state = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::STORYBOARD_VIDEO['value']]);
        $model->narration_state = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::STORYBOARD_NARRATION_VOICE['value']]);
    }
}
