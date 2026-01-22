<?php

namespace plugin\shortplay\api;

use app\expose\api\Install as ApiInstall;

class Install
{
    use ApiInstall;
    public function __construct()
    {
        $this->plugin='shortplay';
        $this->path = __DIR__;
    }
}
