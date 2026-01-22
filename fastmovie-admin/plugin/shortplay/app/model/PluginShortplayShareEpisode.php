<?php

namespace plugin\shortplay\app\model;

use app\model\Basic;
use plugin\control\app\model\PluginChannelsUser;

class PluginShortplayShareEpisode extends Basic
{
    public function channels()
    {
        return $this->hasOne(PluginChannelsUser::class, 'id', 'channels_uid');
    }
    public function drama()
    {
        return $this->hasOne(PluginShortplayDrama::class, 'id', 'drama_id');
    }
    public function episode()
    {
        return $this->hasOne(PluginShortplayDramaEpisode::class, 'id', 'episode_id');
    }
}
