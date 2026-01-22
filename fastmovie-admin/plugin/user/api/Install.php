<?php

namespace plugin\user\api;

use app\expose\api\Install as ApiInstall;

class Install
{
    use ApiInstall;
    public function __construct()
    {
        $this->plugin='user';
        $this->path = __DIR__;
    }
}
