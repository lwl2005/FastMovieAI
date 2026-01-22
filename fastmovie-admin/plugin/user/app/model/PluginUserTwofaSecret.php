<?php

namespace plugin\user\app\model;

use app\model\Basic;

class PluginUserTwofaSecret extends Basic
{
    protected function sceneExternal()
    {
        return $this->visible(['id','totp_app','is_default','create_time','totp_app_text']);
    }
}
