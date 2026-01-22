<?php

namespace plugin\user\app\api\controller;

use app\Basic;
use app\expose\trait\Uploads;

class UploadsController extends Basic
{
    use Uploads;
    public function __construct()
    {
        $this->uid = request()->uid;
    }
}