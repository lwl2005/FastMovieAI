<?php

namespace plugin\finance\utils\enum;

use app\expose\enum\builder\Enum;

class OrdersState extends Enum
{
    const WAIT_PAY = [
        'label' => '待支付',
        'value' => 0,
        'props' => [
            'type' => 'info'
        ]
    ];
    const PAID = [
        'label' => '已支付',
        'value' => 10,
        'props' => [
            'type' => 'warning'
        ]
    ];
    const WAIT_VERIFIED = [
        'label' => '待验证',
        'value' => 11,
        'props' => [
            'type' => 'warning'
        ]
    ];
    const FINISH = [
        'label' => '已完成',
        'value' => 20,
        'props' => [
            'type' => 'success'
        ]
    ];
    const REFUND_ING = [
        'label' => '退款中',
        'value' => 30,
        'props' => [
            'type' => 'warning'
        ]
    ];
    const REFUND_SUCCESS = [
        'label' => '退款成功',
        'value' => 40,
        'props' => [
            'type' => 'success'
        ]
    ];
    const REFUND_FAIL = [
        'label' => '退款失败',
        'value' => 50,
        'props' => [
            'type' => 'danger'
        ]
    ];
    const INVALID = [
        'label' => '作废',
        'value' => 60,
        'props' => [
            'type' => 'danger'
        ]
    ];
    const NOTIFY_FAIL = [
        'label' => '通知失败',
        'value' => 70,
        'props' => [
            'type' => 'danger'
        ]
    ];
    const CANCEL = [
        'label' => '已取消',
        'value' => 99,
        'props' => [
            'type' => 'info'
        ]
    ];
}
