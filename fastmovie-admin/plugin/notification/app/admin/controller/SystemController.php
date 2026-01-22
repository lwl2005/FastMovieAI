<?php

namespace plugin\notification\app\admin\controller;

use app\Basic;
use app\expose\trait\Config;
use support\Request;

class SystemController extends Basic
{
    use Config;
    public function strategy(Request $request)
    {
        return $this->builder();
    }
    public function push(Request $request)
    {
        return $this->builder();
    }
    public function wechat_template(Request $request)
    {
        return $this->builder();
    }
    public function sms_template(Request $request)
    {
        return $this->builder();
    }
}
