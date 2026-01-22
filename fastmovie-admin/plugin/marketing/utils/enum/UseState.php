<?php

namespace plugin\marketing\utils\enum;

use app\expose\enum\builder\Enum;

class UseState extends Enum
{
    const ON= [
        'label' => '可用',
        'value' => 0,
        'props' => [
            'type' => 'success'
        ]
    ];
    const USED = [
        'label' => '已使用',
        'value' => 10,
        'props' => [
            'type' => 'info'
        ]
    ];
    const EXPIRE= [
        'label' => '过期',
        'value' => 99,
        'props' => [
            'type' => 'danger'
        ]
    ];
}
