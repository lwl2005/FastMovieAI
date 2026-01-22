<?php

namespace plugin\shortplay\app\model;

use app\model\Basic;

class PluginShortplayDramaStoryboardProp extends Basic
{
    public function prop()
    {
        return $this->hasOne(PluginShortplayProp::class, 'id', 'prop_id');
    }
}
