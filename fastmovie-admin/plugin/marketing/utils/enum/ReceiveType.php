<?php

namespace plugin\marketing\utils\enum;

use app\expose\enum\builder\Enum;

class ReceiveType extends Enum
{
    const REPEAT_DAY = [
        'label' => '每日可领',
        'value' => 'repeat_day',
        'props' => [
            'type' => 'primary'
        ]
    ];
    const REPEAT_WEEK= [
        'label' => '每周可领',
        'value' => 'repeat_week',
        'props' => [
            'type' => 'primary'
        ]
    ];
    const REPEAT_MONTH = [
        'label' => '每月可领',
        'value' => 'repeat_month',
        'props' => [
            'type' => 'primary'
        ]
    ];
    const USER_ONLY = [
        'label' => '账户唯一',
        'value' => 'user_only',
        'props' => [
            'type' => 'success'
        ]
    ];
}
