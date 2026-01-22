<?php

namespace plugin\marketing\utils\enum;

use app\expose\enum\builder\Enum;

class BillingCycle extends Enum
{
    const MONTH = [
        'label' => '按月',
        'value' => 'month'
    ];
    const YEAR = [
        'label' => '按年',
        'value' => 'year'
    ];
}
