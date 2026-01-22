<?php

namespace plugin\user\app\admin\controller;

use app\Basic;
use app\expose\trait\Config;

class SettingController extends Basic
{
    use Config;
    public function basic()
    {
        return $this->builder();
    }
}