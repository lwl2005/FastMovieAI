<?php

namespace plugin\notification\utils\enum;


use app\expose\enum\builder\Enum;

class Method extends Enum
{
    const WECHAT_AND_SMS = [
        'label' => '微信+短信',
        'value' => 'wechat_and_sms'
    ];
    const WECHAT_FIRST = [
        'label' => '微信优先',
        'value' => 'wechat_first'
    ];
    const SMS_FIRST = [
        'label' => '短信优先',
        'value' => 'sms_first'
    ];
    const WECHAT_ONLY = [
        'label' => '仅微信',
        'value' => 'wechat_only'
    ];
    const SMS_ONLY = [
        'label' => '仅短信',
        'value' => 'sms_only'
    ];
}
