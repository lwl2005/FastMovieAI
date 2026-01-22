<?php

namespace plugin\shortplay\app\model;

use plugin\control\expose\helper\Uploads;
use app\model\Basic;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;
use plugin\shortplay\utils\enum\PropStatus;

class PluginShortplayProp extends Basic
{
    public function getThreeViewImageAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setThreeViewImageAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
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
        $model->status_enum =PropStatus::get($model->status);
        if ($model->status != PropStatus::GENERATED['value']) {
            $imageState = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::PROP_IMAGE['value']]);
            $threeViewImageState = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::PROP_THREE_VIEW_IMAGE['value']]);
            if ($imageState > 0 || $threeViewImageState > 0) {
                $model->status_enum = PropStatus::PENDING;
            }
        }
    }
}
