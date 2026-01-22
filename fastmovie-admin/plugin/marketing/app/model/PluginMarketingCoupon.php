<?php

namespace plugin\marketing\app\model;

use app\expose\utils\Str;
use app\model\Basic;
use plugin\developer\app\model\PluginDeveloperTeam;
use plugin\marketing\utils\enum\CouponRule;
use plugin\user\app\model\PluginUser;

class PluginMarketingCoupon extends Basic
{
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
    public function password()
    {
        return $this->hasMany(PluginMarketingCouponPassword::class, 'id', 'coupon_id');
    }
    public function server()
    {
        return $this->hasMany(PluginMarketingCouponServer::class, 'coupon_id', 'id');
    }
    /* public function user()
    {
        return $this->hasOne();
    } */
    public static function options($where = [])
    {
        $models = self::where($where)->order('id desc')->select();
        $data = [];
        foreach ($models as $item) {
            $data[] = [
                'label' => $item->title,
                'value' => $item->id,
                'tips' => "折扣：{$item->discount_text}"
            ];
        }
        return $data;
    }
    protected function sceneExternal()
    {
        return $this->visible(['title', 'coupon_rule', 'discount', 'full_price', 'money', 'receive_type', 'use_type', 'stackable', 'num', 'receive_num', 'sum', 'discount_text', 'code', 'percentage', 'receive', 'exclusive', 'scope', 'server', 'receive_type_text', 'use_type_text']);
    }
    public static function onAfterRead($model)
    {
        if ($model->coupon_rule) {
            if ($model->coupon_rule === CouponRule::DISCOUNT['value']) {
                $model->discount_text = ($model->discount * 10) . '折';
            } else {
                if ($model->full_price > 0) {
                    $model->discount_text = "满{$model->full_price}元减{$model->money}元";
                } else {
                    $model->discount_text = "直减{$model->money}元";
                }
            }
        }
        // coupon_id转16进制
        $coupon_id = dechex((int)$model->id);
        // coupon_id长度为6位，不足6位前面补0
        $model->code = 'A' . str_pad($coupon_id, 6, '0', STR_PAD_LEFT);
    }
    public static function codeToId($code)
    {
        return hexdec(substr($code, 1));
    }

    /**
     * 获取优惠券码
     */
    public static function getCouponCode($prefix, $coupon_id)
    {
        // coupon_id转16进制
        $coupon_id = dechex((int)$coupon_id);
        // coupon_id长度为6位，不足6位前面补0
        $coupon_id = str_pad($coupon_id, 6, '0', STR_PAD_LEFT);
        $createDate = date('yWz');
        $round = Str::random(6);
        // 生成优惠券码
        $code = "{$prefix}{$coupon_id}-{$createDate}-{$round}";
        $couponCode = PluginMarketingCouponCode::where(['coupon_code' => $code])->find();
        if ($couponCode) {
            self::getCouponCode($prefix, $coupon_id);
        }
        return $code;
    }
}
