<?php

namespace plugin\model\utils\enum;

use app\expose\enum\builder\Enum;

class ModelType extends Enum
{
    const CHAT = [
        'label' => '对话',
        'value' => 'chat',
        'props' => [
            'type' => 'primary',
        ]
    ];
    const DRAW = [
        'label' => '绘图',
        'value' => 'draw',
        'props' => [
            'type' => 'warning',
        ]
    ];
    const TOVIDEO = [
        'label' => '视频生成',
        'value' => 'tovideo',
        'props' => [
            'type' => 'success',
        ]
    ];
    const AUDIO = [
        'label' => '音频生成',
        'value' => 'audio',
        'props' => [
            'type' => 'danger',
        ]
    ];
}
