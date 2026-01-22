<?php

namespace plugin\marketing\api;

use app\expose\api\Install as ApiInstall;

class Install
{
    use ApiInstall;
    public function __construct()
    {
        $this->path = __DIR__;
        $this->plugin = 'marketing';
    }
}
