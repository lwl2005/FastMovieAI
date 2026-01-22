<?php

namespace plugin\shortplay\app\api\controller;

use app\Basic;
use app\expose\enum\State;
use plugin\shortplay\app\model\PluginShortplayStyle;
use support\Request;

class StyleController extends Basic
{
    protected $notNeedLogin = ['index'];
    public function index(Request $request)
    {
        $where = [];
        $where[] = ['state', '=', State::YES['value']];
        $name = $request->get('name');
        if ($name) {
            $where[] = ['name', 'like', '%' . $name . '%'];
        }
        $classify = $request->get('classify','all');
        if ($classify != 'all') {
            $where[] = ['classify', '=', $classify];
        }
        $PluginShortplayStyle = PluginShortplayStyle::where($where)->order('id', 'desc')->select();
        return $this->resData($PluginShortplayStyle);
    }
}
