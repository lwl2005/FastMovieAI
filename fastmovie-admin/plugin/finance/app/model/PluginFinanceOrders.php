<?php

namespace plugin\finance\app\model;

use app\expose\enum\EventName;
use app\expose\enum\PaymentChannels;
use plugin\control\expose\helper\Uploads;
use app\expose\utils\Str;
use app\model\Basic;
use plugin\finance\expose\helper\Account;
use plugin\finance\utils\enum\OrdersState;
use plugin\finance\utils\enum\OrdersType;
use plugin\finance\utils\enum\PointsBillScene;
use plugin\marketing\app\model\PluginMarketingCoupon;
use plugin\marketing\app\model\PluginMarketingCouponCode;
use plugin\marketing\app\model\PluginMarketingPlan;
use plugin\marketing\app\model\PluginMarketingPlanPrice;
use plugin\marketing\app\model\PluginMarketingPoints;
use plugin\marketing\utils\enum\UseState;
use plugin\user\app\model\PluginUser;
use plugin\user\app\model\PluginUserVip;
use think\facade\Db;
use think\model\concern\SoftDelete;
use Webman\Event\Event;

class PluginFinanceOrders extends Basic
{
    use SoftDelete;
    protected function getOptions(): array
    {
        return [
            'type' => [
                // 设置JSON字段的类型
                'origin_money'    =>    'float',
                'unit_money'    =>    'float',
                'money'    =>    'float',
                'system_money' => 'float',
                'channels_money' => 'float',
                'developer_money' => 'float',
                'coupon' => 'json'
            ]
        ];
    }
    public function getTransferVoucherImageAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setTransferVoucherImageAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function user()
    {
        return $this->hasOne(PluginUser::class, 'id', 'uid');
    }
    protected function sceneExternal()
    {
        return $this->visible(['title', 'create_time', 'update_time', 'trade_no', 'origin_money', 'money', 'num', 'pay_type', 'state', 'state_text', 'pay_time', 'type', 'cancel_time', 'expire_time', 'comment_time', 'coupon', 'apps', 'pay_type_text', 'user', 'site']);
    }
    public static function onBeforeInsert($model)
    {
        $model->trade_no = self::generateTradeNo();
    }
    public static function generateTradeNo()
    {
        $get_random = Str::random(8, 1);
        $date = date('YmdHis');
        $trade_no = "{$date}{$get_random}";
        while (self::where('trade_no', '=', $trade_no)->find()) {
            $trade_no = self::generateTradeNo();
        }
        return $trade_no;
    }
    public static function finish($where)
    {
        Db::startTrans();
        try {
            $Orders = self::where($where)->find();
            if (!$Orders) {
                throw new \Exception('订单不存在');
            }
            $notify = null;
            switch ($Orders->payment_channels) {
                case PaymentChannels::WXPAY['value']:
                    $notify = PluginFinancePaymentNotify::where(['transaction_id' => $Orders->transaction_id])->find();
                    break;
            }
            switch ($Orders->type) {
                case OrdersType::POINTS['value']:
                    $pointsInfo = PluginMarketingPoints::where(['id' => $Orders->alias_id])->find();
                    if (!$pointsInfo) {
                        throw new \Exception('积分套餐不存在');
                    }
                    $points = (int)$pointsInfo->points + (int)$pointsInfo->give;
                    Account::incPoints($Orders->uid, $Orders->channels_uid, $points, PointsBillScene::RECHARGE['value'], $Orders->id, '购买套餐：' . $pointsInfo->name, true);
                    break;
                case OrdersType::VIP['value']:
                    $vipInfoPrice = PluginMarketingPlanPrice::where(['id' => $Orders->alias_id])->find();
                    if (!$vipInfoPrice) {
                        throw new \Exception('会员套餐不存在');
                    }
                    $vipInfo = PluginMarketingPlan::where(['id' => $vipInfoPrice->plan_id])->find();
                    if (!$vipInfo) {
                        throw new \Exception('会员套餐不存在');
                    }
                    // 会员完成逻辑
                    self::vip($Orders->alias_id, $Orders->uid, $Orders->channels_uid, $Orders->id);
                    break;
                default:
                    throw new \Exception('订单类型错误');
            }
            $Orders->state = OrdersState::FINISH['value'];
            $Orders->finish_time = date('Y-m-d H:i:s');
            $Orders->save();
            PluginFinanceOrdersLog::info($Orders, '订单处理完成');
            if ($notify) {
                $notify->state = OrdersState::FINISH['value'];
                $notify->save();
            }
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw $th;
        }
        Event::emit(EventName::ORDERS_FINISH['value'], $Orders);
    }
    public static function cancel($model)
    {
        $model->state = OrdersState::CANCEL['value'];
        $model->cancel_time = date('Y-m-d H:i:s');
        $model->save();
        if (!empty($model->coupon)) {
            $PluginMarketingCouponCodes = PluginMarketingCouponCode::whereIn('id', $model->coupon)->select();
            if (!$PluginMarketingCouponCodes->isEmpty()) {
                foreach ($PluginMarketingCouponCodes as $coupon) {
                    $coupon->state = UseState::ON['value'];
                    $coupon->use_time = null;
                    $coupon->save();
                    $appCoupon = PluginMarketingCoupon::where('id', $coupon->coupon_id)->find();
                    $appCoupon->use_num = Db::raw('use_num-1');
                    $appCoupon->save();
                }
            }
        }
    }


    private static function vip($alias_id, $uid, $channels_uid, $order_id)
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
