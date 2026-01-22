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
            if ($order->type == OrdersType::POINTS['value']) {
                $this->points($order->alias_id, $order->uid, $order->channels_uid, $order->id);
            } elseif ($order->type == OrdersType::VIP['value']) {
                $this->vip($order->alias_id, $order->uid, $order->channels_uid, $order->id);
            }
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw $th;
        }
    }

    private function points($alias_id, $uid, $channels_uid, $order_id)
    {
        $pointsInfo = PluginMarketingPoints::where(['id' => $alias_id])->find();
        if (!$pointsInfo) {
            return false;
        }
        $points = (int)$pointsInfo->points + (int)$pointsInfo->give;
        Account::incPoints($uid, $channels_uid, $points, PointsBillScene::RECHARGE['value'], $order_id, '购买套餐：' . $pointsInfo->name);
    }

    private function vip($alias_id, $uid, $channels_uid, $order_id)
    {
        $vipInfoPrice = PluginMarketingPlanPrice::where(['id' => $alias_id])->find();
        if (!$vipInfoPrice) {
            return false;
        }
        $vipInfo = PluginMarketingPlan::where(['id' => $vipInfoPrice->plan_id])->find();
        if (!$vipInfo) {
            return false;
        }
        $vip = new PluginUserVip();
        $vip->uid = $uid;
        $vip->channels_uid = $channels_uid;
        $vip->plan_id = $vipInfo->id;
        $vip->plan_price_id = $vipInfoPrice->id;
        $vip->num = $vipInfo->key == 'basic' ? 2 : 11;
        $vip->key = $vipInfo->key;
        $vip->save();
        $time = date('Y-m-d H:i:s', strtotime('+ ' . $vipInfo->num  + 1 . ' month'));
        Account::incPoints($uid, $channels_uid, $vipInfoPrice->points + $vipInfoPrice->give, PointsBillScene::RECHARGE['value'], $order_id, '购买会员：' . $vipInfo->name, true, $time);
    }
}
