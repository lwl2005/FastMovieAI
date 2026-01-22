<?php

namespace plugin\user\utils\enum;

use app\expose\enum\builder\Enum;

class UserPermission extends Enum
{
    const MOBILE = [
        'label' => '已绑定手机号',
        'value' => 'mobile'
    ];
    const EMAIL = [
        'label' => '已绑定邮箱',
        'value' => 'email'
    ];
    const TWOFA = [
        'label' => '已开启两步验证',
        'value' => 'twofa'
    ];
    const WECHAT = [
        'label' => '已绑定微信',
        'value' => 'wechat'
    ];
    const USERNAME = [
        'label' => '已设置用户名',
        'value' => 'username'
    ];
    const PASSWORD = [
        'label' => '已设置密码',
        'value' => 'password'
    ];
    const REALNAME = [
        'label' => '已实名认证',
        'value' => 'realname'
    ];
    const BUY = [
        'label' => '可以购买',
        'value' => 'buy'
    ];
}