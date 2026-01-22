<?php

namespace plugin\finance\utils\enum;

use app\expose\enum\builder\Enum;

class OrdersLogLevel extends Enum
{
    const INFO = [
        'label' => '普通',
        'value' => 'info',
        'props' => [
            'type' => 'info'
        ]
    ];
    const ERROR = [
        'label' => '错误',
        'value' => 'error',
        'props' => [
            'type' => 'danger'
        ]
    ];
    const WARNING = [
        'label' => '警告',
        'value' => 'warning',
        'props' => [
            'type' => 'warning'
        ]
    ];
    const SUCCESS = [
        'label' => '成功',
        'value' => 'success',
        'props' => [
            'type' => 'success'
        ]
    ];
    const FAIL = [
        'label' => '失败',
        'value' => 'fail',
        'props' => [
            'type' => 'danger'
        ]
    ];
}
