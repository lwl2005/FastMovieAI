<?php

namespace plugin\user\app\api\controller;

use app\Basic;
use plugin\finance\utils\enum\PointsBillScene;
use plugin\user\app\model\PluginUserPointsBill;
use plugin\user\utils\enum\MoneyAction;
use support\Request;

class BillController extends Basic
{
    public function getPointsBill(Request $request)
    {
        $type = $request->get('type', 'all');

        $uid = $request->uid;
        $limit = $request->get('limit', 10);
        $where = [];
        $where[] = ['uid', '=', $uid];
        if ($type != 'all') {
            if ($type == 'consume') {
                $where[] = ['action', '=', MoneyAction::DECREASE['value']];
            }
            if ($type == 'gain') {
                $where[] = ['action', '=', MoneyAction::INCREASE['value']];
            }
            if ($type == 'recharge') {
                $where[] = ['scene', '=', PointsBillScene::RECHARGE['value']];
            }
        }
        $PluginFinanceBill = PluginUserPointsBill::where($where)->order('create_time', 'desc')->paginate($limit);

        return $this->resData($PluginFinanceBill);
    }
}
