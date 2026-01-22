<?php

namespace plugin\user\utils\enum;

use app\expose\enum\builder\Enum;

class VcodeScene extends Enum
{
    const LOGIN = [
        'label' => '登录',
        'value' => 'login',
        'variable' => ['code' => '验证码', 'expire' => '过期时间']
    ];
    const SIGNUP = [
        'label' => '注册',
        'value' => 'signup',
        'variable' => ['code' => '验证码', 'expire' => '过期时间']
    ];
    const BIND_MOBILE = [
        'label' => '绑定手机号',
        'value' => 'bind_mobile',
        'variable' => ['username' => '用户昵称', 'code' => '验证码', 'expire' => '过期时间']
    ];
    const BIND_EMAIL = [
        'label' => '绑定邮箱',
        'value' => 'bind_email',
        'variable' => ['username' => '用户昵称', 'code' => '验证码', 'expire' => '过期时间']
    ];
    const SET_PASSWORD = [
        'label' => '设置密码',
        'value' => 'set_password',
        'variable' => ['username' => '用户昵称', 'code' => '验证码', 'expire' => '过期时间','password' => '密码']
    ];
}
