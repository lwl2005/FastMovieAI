<?php

namespace plugin\shortplay\app\model;

use app\model\Basic;
use plugin\control\expose\helper\Uploads;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;

class PluginShortplayDramaScene extends Basic
{
    public function getImageAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setImageAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public static function onAfterRead($model)
    {
        $model->image_state = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::SCENE_IMAGE['value']]);
    }
}
