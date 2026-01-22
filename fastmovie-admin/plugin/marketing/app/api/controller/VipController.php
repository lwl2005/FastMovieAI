<?php

namespace plugin\marketing\app\api\controller;

use app\Basic;
use app\expose\enum\State;
use plugin\marketing\app\model\PluginMarketingPlan;
use support\Request;

class VipController extends Basic
{
    public function index(Request $request)
    {
        $channels_uid = $request->channels_uid;
        $where = [];
        $where[] = ['channels_uid', '=', $channels_uid];
        $where[] = ['state', '=', State::YES['value']];
        $list = PluginMarketingPlan::with('price')->where($where)->order('id desc')->select();
        return $this->resData($list);
    }
}
