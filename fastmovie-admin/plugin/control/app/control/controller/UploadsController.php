<?php

namespace plugin\control\app\control\controller;

use app\Basic;
use app\expose\helper\Config;
use plugin\control\expose\trait\Uploads;

class UploadsController extends Basic
{
    use Uploads;
    public function __construct()
    {
        $request = request();
        $this->channels_uid = $request->channels_uid;
        $this->admin_uid = $request->uid;
        $Config = new Config('filesystem', '', $this->channels_uid);
        if (empty($Config->toArray())) {
            throw new \Exception('请先完善上传配置');
        }
    }
}
