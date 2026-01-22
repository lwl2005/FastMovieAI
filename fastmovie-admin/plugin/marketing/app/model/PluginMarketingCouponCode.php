<?php

namespace plugin\marketing\app\model;

use app\model\Basic;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\finance\utils\enum\OrdersState;
use plugin\marketing\utils\enum\CouponRule;
use plugin\marketing\utils\enum\UseType;
use plugin\user\app\model\PluginUser;

class PluginMarketingCouponCode extends Basic
{
    // 所有可识别订单状态
    public static $validOrderStates = [
        OrdersState::WAIT_PAY['value'],
        OrdersState::PAID['value'],
        OrdersState::FINISH['value'],
        OrdersState::REFUND_ING['value'],
        OrdersState::REFUND_SUCCESS['value'],
        OrdersState::REFUND_FAIL['value'],
        OrdersState::INVALID['value'],
        OrdersState::NOTIFY_FAIL['value'],
    ];
    protected function getOptions(): array
    {
        return [
            'type' => [
                // 设置JSON字段的类型
                'discount'    =>    'float',
                'full_price'    =>    'float',
                'money'    =>    'float',
            ]
        ];
    }
    public function user()
    {
        return $this->hasOne(PluginUser::class, 'id', 'uid');
    }
    public function coupon()
    {
        return $this->hasOne(PluginMarketingCoupon::class, 'id', 'coupon_id');
    }
    protected function sceneExternal()
    {
        return $this->visible(['coupon_code', 'coupon_rule', 'state', 'state_text', 'discount', 'full_price', 'money', 'start_time', 'end_time', 'expire_time', 'receive_time', 'coupon', 'exclusive', 'scope', 'update_scope', 'support_scope', 'role', 'receive_type_text', 'use_type_text', 'server']);
    }
    /**
     * 使用优惠券
     * @param PluginFinanceOrders 订单
     * @param PluginMarketingCouponCode 所选优惠券列表
     * @param null $extra 额外参数
     * @return array 返回可使用优惠券码id
     * @throws \Exception
     */
    public static function useCoupon($Order, $Coupons, $extra = null)
    {
        $Order->system_money = 0;
        $result = [];

        foreach ($Coupons as $item) {
            $coupon = $item->coupon;
            $server = $coupon->server()->column('server');
            if (!empty($server) && !in_array($Order->type, $server)) {
                throw new \Exception("优惠券「{$coupon->title}」不适用于当前服务");
            }

            // 3. 校验使用类型
            switch ($coupon->use_type) {
                case UseType::FIRST_ORDER['value']:
                    $hasPaidOrder = PluginFinanceOrders::where(['uid' => $Order->uid])
                        ->whereIn('state', self::$validOrderStates)
                        ->count();
                    if ($hasPaidOrder > 0) {
                        throw new \Exception("优惠券「{$coupon->title}」仅限首单使用");
                    }
                    break;
            }

            // 5. 优惠金额计算（先复制当前金额）
            $newMoney = $Order->money;

            switch ($coupon->coupon_rule) {
                case CouponRule::DISCOUNT['value']:
                    if ($coupon->discount > 0 && $coupon->discount < 1) {
                        $newMoney = $newMoney * $coupon->discount;
                    }
                    break;

                case CouponRule::FULL_PRICE['value']:
                    if ($coupon->full_price > 0 && $Order->origin_money < $coupon->full_price) {
                        throw new \Exception("优惠券「{$coupon->title}」不满足满减条件");
                    }
                    $newMoney -= $coupon->money;
                    break;
            }

            $newMoney = max(0, $newMoney);
            $discountAmount = $Order->money - $newMoney;

            // 6. 更新订单金额及归属
            $Order->money = $newMoney;
            $Order->system_money += $discountAmount;

            // 7. 记录已使用的券码ID
            $result[] = $item->id;
        }

        return $result;
    }
}
