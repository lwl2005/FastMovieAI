<?php

namespace plugin\control\app\control\controller;

use app\Basic;
use app\expose\trait\Config;
use support\Request;

class PlatformController extends Basic
{
    use Config;
    public function wechat_miniproject(Request $request)
    {
        $this->channels_uid = $request->channels_uid;
        return $this->builder();
    }
    public function wechat_official_account(Request $request)
    {
        $this->channels_uid = $request->channels_uid;
        return $this->builder();
    }
}
