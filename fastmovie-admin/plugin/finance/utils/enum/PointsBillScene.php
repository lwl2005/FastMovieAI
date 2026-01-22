<?php

namespace plugin\finance\utils\enum;

use app\expose\enum\builder\Enum;

class PointsBillScene extends Enum
{
    const ADMIN = [
        'label' => '管理员操作',
        'value' => 'admin',
        'props' => [
            'type' => 'info'
        ]
    ];
    const RECHARGE = [  
        'label' => '充值',
        'value' => 'recharge',
        'props' => [
            'type' => 'success'
        ]
    ];
    const VIP_UPGRADE = [
        'label' => 'VIP升级',
        'value' => 'vip_upgrade',
        'props' => [
            'type' => 'warning'
        ]
    ];
    const REGISTER = [
        'label' => '注册',
        'value' => 'register',
        'props' => [
            'type' => 'primary'
        ]
    ];
    const INVITE = [
        'label' => '邀请',
        'value' => 'invite',
        'props' => [
            'type' => 'success'
        ]
    ];
    const CONSUME=[
        'label' => '消费',
        'value' => 'consume',
        'props' => [
            'type' => 'danger'
        ]
    ];
}
