<?php

namespace plugin\finance\app\admin\controller;

use app\Basic;
use app\expose\trait\Config;

class SettingsController extends Basic
{
    use Config;
    public function basic()
    {
        return $this->builder();
    }
}