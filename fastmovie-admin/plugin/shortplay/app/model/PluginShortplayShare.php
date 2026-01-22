<?php

namespace plugin\shortplay\app\model;

use app\model\Basic;
use plugin\control\app\model\PluginChannelsUser;
use plugin\user\app\model\PluginUser;

class PluginShortplayShare extends Basic
{
    public function channels()
    {
        return $this->hasOne(PluginChannelsUser::class, 'id', 'channels_uid');
    }
    public function user()
    {
        return $this->hasOne(PluginUser::class, 'id', 'uid');
    }
    public function drama()
    {
        return $this->hasOne(PluginShortplayDrama::class, 'id', 'drama_id');
    }
}
