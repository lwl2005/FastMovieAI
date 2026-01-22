<?php

namespace plugin\user\app\control\controller;

use app\Basic;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\enum\Filesystem;
use app\expose\enum\SubmitEvent;
use app\expose\trait\Config;
use app\model\Config as ModelConfig;
use support\Request;

class SystemController extends Basic
{
    use Config;
    public function register(Request $request)
    {
        $this->channels_uid = $request->channels_uid;
        return $this->builder();
    }
}
