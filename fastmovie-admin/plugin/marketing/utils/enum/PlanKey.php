<?php

namespace plugin\marketing\utils\enum;

use app\expose\enum\builder\Enum;

class PlanKey extends Enum
{
    const BASIC = [
        'label' => '基础版',
        'value' => 'basic'
    ];
    const PRO = [
        'label' => '专业版',
        'value' => 'pro'
    ];
}
