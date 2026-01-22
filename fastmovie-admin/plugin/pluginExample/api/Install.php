<?php

namespace plugin\pluginExample\api;

use app\expose\api\Install as ApiInstall;

class Install
{
    use ApiInstall;
    public function __construct()
    {
        $this->plugin='pluginExample';
        $this->path = __DIR__;
    }
}
