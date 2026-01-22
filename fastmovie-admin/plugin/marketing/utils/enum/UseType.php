<?php

namespace plugin\marketing\utils\enum;

use app\expose\enum\builder\Enum;

class UseType extends Enum
{
    const UNLIMITED= [
        'label' => '无限制',
        'value' => 'unlimited',
        'props' => [
            'type' => 'danger'
        ]
    ];
    const FIRST_ORDER = [
        'label' => '首单可用',
        'value' => 'first_order',
        'props' => [
            'type' => 'success'
        ]
    ];
}
