<?php

namespace plugin\marketing\app\validate;

use app\expose\validate\Validate;

class PluginMarketingCoupon extends Validate
{
    protected $rule = [
        'title' => 'require',
        'coupon_rule' => 'require',
        'discount' => 'requireIf:coupon_rule,discount',
        'full_price' => 'requireIf:coupon_rule,full_price',
        'money' =>  'requireIf:coupon_rule,full_price',
        'receive_type' => 'require',
        'use_type' => 'require',
    ];

    protected $message = [
        'title.require' => '请输入优惠券名',
        'coupon_rule.require'=>'请选择优惠策略',
        'discount.requireIf'=>'请输入折扣',
        'full_price.requireIf'=>'请输入满减策略',
        'money.requireIf'=>'请输入满减策略',
        'receive_type.require'=>'请选择领取规则',
        'use_type.require'=>'请选择使用规则',
    ];
}
