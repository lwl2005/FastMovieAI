<?php

namespace plugin\marketing\process;

use plugin\marketing\app\model\PluginMarketingCoupon;
use plugin\marketing\app\model\PluginMarketingCouponCode;
use plugin\marketing\utils\enum\UseState;
use support\Log;
use Workerman\Crontab\Crontab;
use think\facade\Db;

class Expire
{
    public function onWorkerStart()
    {
        // 每秒钟执行一次
        new Crontab('*/10 * * * * *', function () {
            try {
                $data = PluginMarketingCouponCode::where(['state' => UseState::ON['value']])->whereNull('uid')->whereTime('end_time', '<=', date('Y-m-d H:i:s'))->limit(20)->select();
                foreach ($data as $item) {
                    Db::startTrans();
                    try {
                        $PluginMarketingCoupon = PluginMarketingCoupon::where(['id' => $item->coupon_id])->find();
                        $PluginMarketingCoupon->num = Db::raw('num-1');
                        $PluginMarketingCoupon->expire_num = Db::raw('expire_num+1');
                        $PluginMarketingCoupon->save();
                        $item->delete();
                        Db::commit();
                    } catch (\Throwable $th) {
                        Db::rollback();
                        Log::error('Process Coupon Expire Error:'.$th->getMessage(),$th);
                    }
                }
                $data = PluginMarketingCouponCode::where(['state' => UseState::ON['value']])->whereNotNull('uid')->whereTime('expire_time', '<=', date('Y-m-d H:i:s'))->limit(20)->select();
                foreach ($data as $item) {
                    Db::startTrans();
                    try {
                        $PluginMarketingCoupon = PluginMarketingCoupon::where(['id' => $item->coupon_id])->find();
                        $PluginMarketingCoupon->expire_num = Db::raw('expire_num+1');
                        $PluginMarketingCoupon->save();
                        $item->state=UseState::EXPIRE['value'];
                        $item->save();
                        Db::commit();
                    } catch (\Throwable $th) {
                        Db::rollback();
                        Log::error('Process UserCoupon Expire Error:'.$th->getMessage(),$th);
                    }
                }
            } catch (\Throwable $th) {
            }
        });
    }
}
