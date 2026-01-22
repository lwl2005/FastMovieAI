<?php

namespace plugin\finance\utils\enum;

use app\expose\enum\builder\Enum;

class OrdersType extends Enum
{
    const POINTS = [
        'label' => '充值',
        'value' => 'points',
        'props' => [
            'type' => 'danger',
            'allow_payment' => ['wxpay', 'alipay']
        ]
    ];
    const VIP = [
        'label' => '会员',
        'value' => 'vip',
        'props' => [
            'type' => 'success',
            'allow_payment' => ['wxpay', 'alipay']
        ]
    ];
}
