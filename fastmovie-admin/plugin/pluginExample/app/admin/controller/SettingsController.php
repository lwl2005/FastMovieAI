<?php

namespace plugin\pluginExample\app\admin\controller;

use app\Basic;
use app\expose\trait\Config;
use support\Request;

class SettingsController extends Basic
{
    use Config;
    public function basic(Request $request)
    {
        return $this->builder();
    }
}