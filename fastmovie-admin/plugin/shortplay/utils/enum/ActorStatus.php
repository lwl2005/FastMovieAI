<?php

namespace plugin\shortplay\utils\enum;

use app\expose\enum\builder\Enum;

class ActorStatus extends Enum
{
    const INITIALIZING = [
        'label' => '待初始化',
        'value' => 'initializing',
        'props' => [
            'type' => 'primary'
        ]
    ];
    const PENDING = [
        'label' => '初始化中',
        'value' => 'pending',
        'props' => [
            'type' => 'warning'
        ]
    ];
    const GENERATED = [
        'label' => '已生成',
        'value' => 'generated',
        'props' => [
            'type' => 'success'
        ]
    ];
    const FAILED = [
        'label' => '生成失败',
        'value' => 'failed',
        'props' => [
            'type' => 'danger'
        ]
    ];
    const CANCELLED = [
        'label' => '已取消',
        'value' => 'cancelled',
        'props' => [
            'type' => 'info'
        ]
    ];
}
