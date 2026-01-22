<?php

namespace plugin\finance\event;

use app\expose\enum\EventName;
use app\expose\enum\PaymentChannels;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\finance\app\model\PluginFinanceOrdersLog;
use plugin\finance\app\model\PluginFinancePaymentNotify;
use plugin\finance\utils\enum\OrdersState;
use support\Log;
use Webman\Event\Event;

class Orders
{
    public static function pay($data)
    {
        if($data['plugin']!=='finance'){
            return true;
        }
        switch($data['payment_channels']){
            case PaymentChannels::WXPAY['value']:
                self::transaction($data['data']);
                break;
        }
    }
    public static function transaction($data)
    {
        if(PluginFinancePaymentNotify::where(['transaction_id'=>$data['transaction_id']])->count()){
            return true;
        }
        $PluginFinancePaymentNotify=new PluginFinancePaymentNotify;
        $PluginFinancePaymentNotify->mchid=$data['mchid'];
        $PluginFinancePaymentNotify->appid=$data['appid'];
        $PluginFinancePaymentNotify->out_trade_no=$data['out_trade_no'];
        $PluginFinancePaymentNotify->transaction_id=$data['transaction_id'];
        $PluginFinancePaymentNotify->trade_type=$data['trade_type'];
        $PluginFinancePaymentNotify->trade_state=$data['trade_state'];
        $PluginFinancePaymentNotify->trade_state_desc=$data['trade_state_desc'];
        $PluginFinancePaymentNotify->bank_type=$data['bank_type'];
        $PluginFinancePaymentNotify->attach=$data['attach'];
        $PluginFinancePaymentNotify->payer_openid=$data['payer']['openid'];
        $PluginFinancePaymentNotify->amount_total=$data['amount']['total'];
        $PluginFinancePaymentNotify->amount_payer_total=$data['amount']['payer_total'];
        $PluginFinancePaymentNotify->amount_currency=$data['amount']['currency'];
        $PluginFinancePaymentNotify->amount_payer_currency=$data['amount']['payer_currency'];
        $PluginFinanceOrders=PluginFinanceOrders::where(['trade'=>$data['out_trade_no']])->find();
        if($PluginFinanceOrders){
            $PluginFinancePaymentNotify->state=OrdersState::PAID['value'];
            $PluginFinancePaymentNotify->save();
            $PluginFinanceOrders->state=OrdersState::PAID['value'];
            $PluginFinanceOrders->pay_type=PaymentChannels::WXPAY['value'];
            $PluginFinanceOrders->pay_time=date('Y-m-d H:i:s');
            $PluginFinanceOrders->transaction_id=$data['transaction_id'];
            $PluginFinanceOrders->save();
            PluginFinanceOrdersLog::info($PluginFinanceOrders, PaymentChannels::WXPAY['label'].'支付成功');
            Event::emit(EventName::ORDERS_PAY_SUCCESS['value'],$PluginFinanceOrders);
        }else{
            $PluginFinancePaymentNotify->state=OrdersState::NOTIFY_FAIL['value'];
            $PluginFinancePaymentNotify->save();
        }
    }
    public static function paySuccess($order)
    {
        try {
            PluginFinanceOrders::finish(['id'=>$order->id]);
        } catch (\Throwable $th) {
            PluginFinanceOrdersLog::fail($order, $th->getMessage());
            Log::error("订单完成失败:{$order->trade_no},{$th->getMessage()},{$th->getFile()}:{$th->getLine()}", $th->getTrace());
            throw $th;
        }
    }
}