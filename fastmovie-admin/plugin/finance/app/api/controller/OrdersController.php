<?php

namespace plugin\finance\app\api\controller;

use app\Basic;
use app\expose\enum\EventName;
use app\expose\enum\PaymentChannels;
use app\expose\enum\ResponseCode;
use app\expose\helper\Payment;
use app\model\PaymentConfig;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\finance\app\model\PluginFinanceOrdersLog;
use plugin\finance\app\model\PluginFinanceWallet;
use plugin\finance\utils\enum\OrdersState;
use plugin\finance\utils\enum\OrdersType;
use plugin\marketing\app\model\PluginMarketingPlan;
use plugin\marketing\app\model\PluginMarketingPlanPrice;
use plugin\marketing\app\model\PluginMarketingPoints;
use support\Log;
use support\Request;
use think\facade\Db;
use Webman\Event\Event;

class OrdersController extends Basic
{
    public function payOrder(Request $request)
    {
        $data = $request->post();
        if (empty($data['trade_no'])) {
            return $this->fail('参数错误');
        }
        $order = PluginFinanceOrders::where(['trade_no' => $data['trade_no'], 'uid' => $request->uid])->find();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->state !== OrdersState::WAIT_PAY['value']) {
            return $this->fail('订单状态错误');
        }
        try {
            return $this->builderPay($request, $order);
        } catch (\Throwable $th) {
            return $this->exception($th);
        }
    }
    public function create(Request $request)
    {
        $data = $request->post();
        if (empty($data['type'])) {
            return $this->fail('参数错误');
        }
        try {
            switch ($data['type']) {
                case OrdersType::POINTS['value']:
                    $order = $this->createPoints($request);
                    break;
                case OrdersType::VIP['value']:
                    $order = $this->createVip($request);
                    break;
                default:
                    throw new \Exception('订单类型错误');
            }
        } catch (\Throwable $th) {
            return $this->exception($th);
        }
        Event::emit(EventName::ORDERS_CREATE['value'], $order);
        try {
            return $this->builderPay($request, $order);
        } catch (\Throwable $th) {
            return $this->exception($th);
        }
    }
    private function builderPay(Request $request, PluginFinanceOrders $order)
    {
        if ($order->state === OrdersState::PAID['value']) {
            try {
                PluginFinanceOrders::finish(['trade_no' => $order->trade_no]);
            } catch (\Throwable $th) {
                PluginFinanceOrdersLog::fail($order, $th->getMessage());
                Log::error("订单完成失败:{$order->trade_no},{$th->getMessage()},{$th->getFile()}:{$th->getLine()}", $th->getTrace());
                throw $th;
            }
            return $this->code(ResponseCode::PAY_SUCCESS, '支付成功');
        } else {
            $data = $request->post();
            if (empty($data['payment_id'])) {
                throw new \Exception('支付方式不存在');
            }
            $PaymentConfig = PaymentConfig::where(['id' => $data['payment_id']])->find();
            if (!$PaymentConfig) {
                throw new \Exception('支付方式不存在');
            }
            $order->plugin = 'finance';
            $order->trade = $order->trade_no;
            $order->price = $order->money;
            switch ($PaymentConfig->channels) {
                case PaymentChannels::WXPAY['value']:
                    $result = Payment::wxPay($order, $PaymentConfig);
                    $orderInfo = [
                        'platform' => $PaymentConfig->platform,
                        'title' => $order->title,
                        'pay_info' => $result,
                        'trade_no' => $order->trade_no,
                        'origin_money' => $order->origin_money,
                        'expire_time' => $order->expire_time,
                        'money' => $order->money
                    ];
                    return $this->resData($orderInfo);
                case PaymentChannels::ALIPAY['value']:
                    break;
                case PaymentChannels::INTEGRAL['value']:
                    break;
                case PaymentChannels::BALANCE['value']:
                    Db::startTrans();
                    try {
                        $order = PluginFinanceOrders::where(['id' => $order->id])->find();
                        if (!$order) {
                            throw new \Exception('订单不存在');
                        }
                        $order->state = OrdersState::PAID['value'];
                        $order->pay_type = PaymentChannels::BALANCE['value'];
                        $order->pay_time = date('Y-m-d H:i:s');
                        $order->save();
                        PluginFinanceWallet::consume($order);
                        PluginFinanceOrdersLog::info($order, '余额支付成功');
                        Db::commit();
                    } catch (\Throwable $th) {
                        Db::rollback();
                        PluginFinanceOrdersLog::fail($order, $th->getMessage());
                        throw $th;
                    }
                    Event::emit(EventName::ORDERS_PAY['value'], [
                        'data' => $order,
                        'payment_channel' => PaymentChannels::BALANCE['value'],
                        'plugin' => 'finance',
                        'template_id' => $PaymentConfig->template_id
                    ]);
                    return $this->builderPay($request, $order);
                default:
                    throw new \Exception('未知支付方式');
            }
        }
    }


    private function createVip(Request $request){
        Db::startTrans();
        try {
            $data = $request->post();
            $vipInfoPrice = PluginMarketingPlanPrice::where(['id' => $data['id']])->find();
            if (!$vipInfoPrice) {
                throw new \Exception('会员套餐不存在');
            }
            $vipInfo = PluginMarketingPlan::where(['id' => $vipInfoPrice->plan_id])->find();
            if (!$vipInfo) {
                throw new \Exception('会员套餐不存在');
            }
            $order = new PluginFinanceOrders;
            $order->uid = $request->uid;
            $order->alias_id = $vipInfoPrice->id;
            $order->title = '购买会员：' . $vipInfo->name;
            $order->origin_money = $vipInfoPrice->price;
            $order->unit_money = $vipInfoPrice->price;
            $order->money = $vipInfoPrice->price;
            $order->num = 1;
            $order->pay_type = $data['pay_type'];
            $order->state = OrdersState::WAIT_PAY['value'];
            $order->type = OrdersType::VIP['value'];
            $order->channels_uid = $request->channels_uid;
            $order->plugin = 'finance';
            $order->expire_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $order->save();
            PluginFinanceOrdersLog::info($order, '创建订单');
            Db::commit();
            return $order;
        } catch (\Throwable $th) {
            Db::rollback();
            throw $th;
        }
    }

    private function createPoints(Request $request)
    {
        Db::startTrans();
        try {
            $data = $request->post();
            $pointsInfo = PluginMarketingPoints::where(['id' => $data['id'], 'channels_uid' => $request->channels_uid])->find();
            if (!$pointsInfo) {
                throw new \Exception('积分套餐不存在');
            }
            $order = new PluginFinanceOrders;
            $order->uid = $request->uid;
            $order->alias_id = $pointsInfo->id;
            $order->title = '购买套餐：' . $pointsInfo->name;
            $order->origin_money = $pointsInfo->price;
            $order->unit_money = $pointsInfo->price;
            $order->money = $pointsInfo->price;
            $order->num = 1;
            $order->pay_type = $data['pay_type'];
            $order->state =  OrdersState::WAIT_PAY['value'];
            $order->type = OrdersType::POINTS['value'];
            $order->channels_uid = $request->channels_uid;
            $order->plugin = 'finance';
            $order->expire_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $order->save();
            PluginFinanceOrdersLog::info($order, '创建订单');
            Db::commit();
            return $order;
        } catch (\Throwable $th) {
            Db::rollback();
            throw $th;
        }
    }

    private function createMoney(Request $request)
    {
        $data = $request->post();
        Db::startTrans();
        try {
            //组装订单数据
            $order = new PluginFinanceOrders;
            $order->uid = $request->uid;
            $order->money = (int)$data['num']; //原价 未实现优惠券逻辑
            if ($order->money < 1) {
                throw new \Exception('充值金额不能小于1元');
            }
            $order->origin_money = $order->money;
            $order->title = '充值金额：' . $order->money . '元';
            $order->state = OrdersState::WAIT_PAY['value'];
            $order['type'] = $data['type'];
            $order->save();
            PluginFinanceOrdersLog::info($order->id, '创建订单');
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw $th;
        }
        return $order;
    }

    /**
     * 查看订单状态  是否支付
     * @Author: 贵州猿创科技有限公司
     * @Date: 2023-05-20
     */
    public function state(Request $request)
    {
        $trade_no = $request->get('trade_no');
        if (!$trade_no) {
            return $this->fail('参数错误');
        }
        $info = PluginFinanceOrders::where(['trade_no' => $trade_no, 'uid' => $request->uid])->find();
        if (!$info) {
            return $this->fail('订单不存在');
        }
        switch ($info->state) {
            case OrdersState::WAIT_PAY['value']:
                return $this->code(ResponseCode::WAIT, '支付中');
            case OrdersState::PAID['value']:
            case OrdersState::FINISH['value']:
                return $this->code(ResponseCode::PAY_SUCCESS, '支付成功');
            case OrdersState::REFUND_ING['value']:
            case OrdersState::REFUND_SUCCESS['value']:
            case OrdersState::REFUND_FAIL['value']:
                return $this->fail('订单已退款或退款中');
            case OrdersState::INVALID['value']:
                return $this->fail('订单已作废');
            case OrdersState::NOTIFY_FAIL['value']:
                return $this->fail('订单通知失败');
            case OrdersState::CANCEL['value']:
                return $this->fail('订单已取消');
            default:
                return $this->fail('订单状态错误');
        }
    }
    public function cancel(Request $request)
    {
        $trade_no = $request->get('trade_no');
        if (!$trade_no) {
            return $this->fail('参数错误');
        }
        try {
            PluginFinanceOrders::cancel(['trade_no' => $trade_no, 'uid' => $request->uid]);
        } catch (\Throwable $th) {
            return $this->fail($th->getMessage());
        }
        return $this->success('取消成功');
    }

    public function getOrderStatus(Request $request)
    {
        $trade_no = $request->get('trade');
        if (!$trade_no) {
            return $this->fail('参数错误');
        }
        $order = PluginFinanceOrders::where(['trade_no' => $trade_no, 'uid' => $request->uid])->find();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        return $this->resData(['status' => $order->state == OrdersState::PAID['value'] ? true : false]);
    }
}
