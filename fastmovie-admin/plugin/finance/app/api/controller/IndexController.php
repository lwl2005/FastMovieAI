<?php

namespace plugin\finance\app\api\controller;

use app\Basic;
use app\expose\enum\PaymentChannels;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\finance\app\model\PluginFinanceOrdersLog;
use plugin\finance\utils\enum\OrdersState;
use plugin\marketing\app\model\PluginMarketingCoupon;
use plugin\marketing\app\model\PluginMarketingCouponCode;
use support\Log;
use support\Request;
use think\facade\Db;

class IndexController extends Basic
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $title = $request->get('title');
        if ($title) {
            $where[] = ['title|trade_no', 'like', "%{$title}%"];
        }
        $state = $request->get('state');
        if ($state !== null) {
            $where[] = ['state', '=', $state];
        }
        $type = $request->get('type');
        if ($type) {
            $where[] = ['type', '=', $type];
        }
        $where[] = ['uid', '=', $request->uid];
        $config = config('plugin.shopwwi.filesystem.app.storage.public');
        $list = PluginFinanceOrders::where($where)->with(['site' => function ($query) {
            $query->field('id,title,logo,domain,ip')->hidden(['id']);
        }])
            ->order('id desc')->paginate($limit)->scene('external')->each(function ($item) use ($config) {
                $item->state_text = OrdersState::get($item->state);
                $pay_type = PaymentChannels::get($item->pay_type);
                if ($pay_type) {
                    $pay_type['icon'] = $config['url'] . $pay_type['icon'];
                    $item->pay_type_text = $pay_type;
                }
                if (!empty($item->coupon)) {
                    $coupon_ids = PluginMarketingCouponCode::whereIn('id', $item->coupon)->column('coupon_id');
                    $item->coupon = PluginMarketingCoupon::whereIn('id', array_unique($coupon_ids))->column('title');
                }
            });
        return $this->resData($list);
    }
    public function details(Request $request)
    {
        $trade_no = $request->get('trade_no');
        $order = PluginFinanceOrders::where('trade_no', $trade_no)->find()->scene('external');
        $order->state_text = OrdersState::get($order->state);
        return $this->resData($order);
    }
    public function OrdersStateEnum(Request $request)
    {
        $list = OrdersState::getOptions();
        return $this->resData($list);
    }
    public function cancel(Request $request)
    {
        $trade_no = $request->post('trade_no');
        $where = [
            ['trade_no', '=', $trade_no],
            ['uid', '=', $request->uid],
            ['state', '=', OrdersState::WAIT_PAY['value']]
        ];
        $order = PluginFinanceOrders::where($where)->find();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        Db::startTrans();
        try {
            PluginFinanceOrders::cancel($order);
            PluginFinanceOrdersLog::info($order, '用户订单取消');
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            Log::error('User Orders Cancel Error:' . $th->getMessage(), $th->getTrace());
            return $this->fail('取消失败');
        }
        return $this->success('取消成功');
    }
    public function delete(Request $request)
    {
        $trade_no = $request->post('trade_no');
        $where = [
            ['trade_no', '=', $trade_no],
            ['uid', '=', $request->uid],
        ];
        $order = PluginFinanceOrders::where($where)->find();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->state == OrdersState::WAIT_PAY['value']) {
            Db::startTrans();
            try {
                PluginFinanceOrders::cancel($order);
                PluginFinanceOrdersLog::info($order, '用户删除订单取消');
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                Log::error('User Orders Delete Error:' . $th->getMessage(), $th->getTrace());
                return $this->fail('删除失败');
            }
        }
        $order->delete();
        return $this->success('删除成功');
    }
}
