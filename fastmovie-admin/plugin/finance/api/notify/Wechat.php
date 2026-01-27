<?php

namespace plugin\finance\api\notify;

use app\Basic;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\finance\expose\helper\Account;
use plugin\finance\utils\enum\OrdersState;
use plugin\finance\utils\enum\OrdersType;
use plugin\finance\utils\enum\PointsBillScene;
use plugin\marketing\app\model\PluginMarketingPlan;
use plugin\marketing\app\model\PluginMarketingPlanPrice;
use plugin\marketing\app\model\PluginMarketingPoints;
use plugin\user\app\model\PluginUserVip;
use think\facade\Db;

class Wechat extends Basic
{
    public function transaction($data)
    {
        Db::startTrans();
        try {
            $order = PluginFinanceOrders::where(['trade_no' => $data['out_trade_no']])->find();
            if (!$order) {
                return false;
            }
            $order->state = OrdersState::PAID['value'];
            $order->pay_time = date('Y-m-d H:i:s');
            $order->finish_time = date('Y-m-d H:i:s');
            $order->transaction_id = $data['transaction_id'];
            $order->save();
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw $th;
        }
    }

    
}
