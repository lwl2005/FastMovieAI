<?php

namespace plugin\user\app\model;

use app\model\Basic;
use plugin\control\app\model\PluginChannelsUser;

class PluginUserPointsBill extends Basic
{
    public function channels()
    {
        return $this->hasOne(PluginChannelsUser::class, 'id', 'channels_uid');
    }
}
