<?php

namespace plugin\user\utils\enum;

use app\expose\enum\builder\Enum;

class MoneyAction extends Enum
{
    const INCREASE = [
        'label' => '增加',
        'value' => 'increase',
        'props'=>[
            'type'=>'danger'
        ]
    ];
    const DECREASE = [
        'label' => '减少',
        'value' => 'decrease',
        'props'=>[
            'type'=>'success'
        ]
    ];
}