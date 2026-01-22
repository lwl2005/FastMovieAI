<?php

namespace plugin\finance\app\model;

use app\model\Basic;
use plugin\finance\utils\enum\OrdersLogLevel;

class PluginFinanceOrdersLog extends Basic
{
    public static function info($order, $remarks,$admin_id=null)
    {
        $log = new self();
        $log->orders_id = $order->id;
        $log->remarks = $remarks;
        $log->level = OrdersLogLevel::INFO['value'];
        $log->admin_id = $admin_id;
        $log->save();
    }
    public static function error($order, $remarks,$admin_id=null){
        $log = new self();
        $log->orders_id = $order->id;
        $log->remarks = $remarks;
        $log->level = OrdersLogLevel::ERROR['value'];
        $log->admin_id = $admin_id;
        $log->save();
    }
    public static function warning($order, $remarks,$admin_id=null){
        $log = new self();
        $log->orders_id = $order->id;
        $log->remarks = $remarks;
        $log->level = OrdersLogLevel::WARNING['value'];
        $log->admin_id = $admin_id;
        $log->save();
    }
    public static function success($order, $remarks,$admin_id=null){
        $log = new self();
        $log->orders_id = $order->id;
        $log->remarks = $remarks;
        $log->level = OrdersLogLevel::SUCCESS['value'];
        $log->admin_id = $admin_id;
        $log->save();
    }
    public static function fail($order, $remarks,$admin_id=null){
        $log = new self();
        $log->orders_id = $order->id;
        $log->remarks = $remarks;
        $log->level = OrdersLogLevel::FAIL['value'];
        $log->admin_id = $admin_id;
        $log->save();
    }
}