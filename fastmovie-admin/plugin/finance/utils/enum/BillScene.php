<?php

namespace plugin\finance\utils\enum;

use app\expose\enum\builder\Enum;

class BillScene extends Enum
{
    const ADMIN = [
        'label' => '管理员操作',
        'value' => 'admin',
        'props' => [
            'type' => 'info'
        ]
    ];
}
