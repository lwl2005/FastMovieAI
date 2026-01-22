<?php

namespace plugin\shortplay\app\model;

use app\model\Basic;
use plugin\control\expose\helper\Uploads;
use plugin\model\app\model\PluginModelTask;
use plugin\shortplay\utils\enum\ActorStatus;

class PluginShortplayActorCharacterLook extends Basic
{
    public function getHeadimgAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setHeadimgAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function getThreeViewImageAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setThreeViewImageAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function characterLook()
    {
        return $this->hasOne(PluginShortplayCharacterLook::class, 'id', 'character_look_id');
    }
    public static function processing($where = [])
    {
        return self::where($where)->whereIn('status', [ActorStatus::INITIALIZING['value'], ActorStatus::PENDING['value']])->count();
    }
    public static function onAfterRead($model)
    {
        $model->status_enum = ActorStatus::get($model->status);
    }
}
