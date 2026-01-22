<?php

namespace plugin\finance\utils\enum;

use app\expose\enum\builder\Enum;

class ValidityPeriod extends Enum
{
    const TEMPORARY = [
        'label' => '临时',
        'value' => 'temporary',
        'props' => [
            'type' => 'info'
        ]
    ];
    const PERMANENT = [  
        'label' => '永久',
        'value' => 'permanent',
        'props' => [
            'type' => 'success'
        ]
    ];
}
