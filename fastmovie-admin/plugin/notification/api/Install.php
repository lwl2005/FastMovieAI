<?php

namespace plugin\notification\api;

use app\expose\api\Install as ApiInstall;

class Install
{
    use ApiInstall;
    public function __construct()
    {
        $this->plugin='notification';
        $this->path = __DIR__;
    }
}
