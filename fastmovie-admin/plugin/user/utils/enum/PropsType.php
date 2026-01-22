<?php

namespace plugin\user\utils\enum;

use app\expose\enum\builder\Enum;

class PropsType extends Enum
{
    const NORMAL = [
        'label' => '无',
        'value' => 'normal',
        'props'=>[
            'type'=>'default'
        ]
    ];
    const PRIMARY = [
        'label' => '主要',
        'value' => 'primary',
        'props'=>[
            'type'=>'primary'
        ]
    ];
    const SUCCESS = [
        'label' => '成功',
        'value' => 'success',
        'props'=>[
            'type'=>'success'
        ]
    ];
    const INFO = [
        'label' => '信息',
        'value' => 'info',
        'props'=>[
            'type'=>'info'
        ]
    ];
    const WARNING = [
        'label' => '警告',
        'value' => 'warning',
        'props'=>[
            'type'=>'warning'
        ]
    ];
    const DANGER = [
        'label' => '危险',
        'value' => 'danger',
        'props'=>[
            'type'=>'danger'
        ]
    ];
}