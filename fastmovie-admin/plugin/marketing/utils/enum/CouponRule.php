<?php

namespace plugin\marketing\utils\enum;

use app\expose\enum\builder\Enum;

class CouponRule extends Enum
{
    const FULL_PRICE = [
        'label' => '满减券',
        'value' => 'full_price'
    ];
    const DISCOUNT = [
        'label' => '打折券',
        'value' => 'discount'
    ];
}
