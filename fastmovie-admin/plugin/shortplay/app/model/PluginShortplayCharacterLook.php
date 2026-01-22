<?php

namespace plugin\shortplay\app\model;

use app\model\Basic;
use plugin\control\expose\helper\Uploads;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;
use plugin\shortplay\utils\enum\ActorStatus;

class PluginShortplayCharacterLook extends Basic
{
    public function getCostumeUrlAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setCostumeUrlAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public static function onAfterRead($model)
    {
        $model->status_enum = ActorStatus::get($model->status);
        if ($model->status != ActorStatus::GENERATED['value']) {
            $imageState = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::CHARACTER_LOOK_COSTUME['value']]);
            if ($imageState > 0) {
                $model->status_enum = ActorStatus::PENDING;
            }
        }
    }
}
