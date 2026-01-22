<?php

namespace plugin\shortplay\app\model;

use plugin\control\expose\helper\Uploads;
use app\model\Basic;
use plugin\control\app\model\PluginChannelsUser;

class PluginShortplayVoice extends Basic
{
    public function getHeadimgAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setHeadimgAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function channels()
    {
        return $this->hasOne(PluginChannelsUser::class, 'id', 'channels_uid');
    }
}
