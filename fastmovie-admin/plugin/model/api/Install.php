<?php

namespace plugin\model\api;

use app\expose\api\Install as ApiInstall;

class Install
{
    use ApiInstall;
    public function __construct()
    {
        $this->plugin='model';
        $this->path = __DIR__;
    }
}
