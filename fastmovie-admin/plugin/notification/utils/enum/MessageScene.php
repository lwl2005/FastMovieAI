<?php

namespace plugin\notification\utils\enum;

use app\expose\enum\builder\Enum;

class MessageScene extends Enum
{
    const ANNOUNCEMENT = [
        'label' => '公告',
        'value' => 'announcement'
    ];
}
