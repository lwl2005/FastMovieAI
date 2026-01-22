<?php

namespace plugin\shortplay\app\api\controller;

use app\Basic;
use plugin\control\expose\trait\Uploads;

class UploadsController extends Basic
{
    use Uploads;
    public function __construct()
    {
        $request = request();
        $this->uid = $request->uid;
        $this->channels_uid = $request->channels_uid;
    }
}
