<?php

namespace plugin\notification\utils\enum;


use app\expose\enum\builder\Enum;

class Scene extends Enum
{
    const TODO = [
        'label' => '待办通知',
        'value' => 'todo',
        'sms_template' => [
            'admin' => '您有新的待办事项，请登录控制台查看。',
            'user' => '您有新的待办事项，请登录控制台查看。'
        ],
        'params' => [
            'template_id_short' => '52305',
            'keywords' => ['项目名称', '客户名称', '提醒时间']
        ]
    ];
    const WARNING = [
        'label' => '预警通知',
        'value' => 'warning',
        'sms_template' => [
            'admin' => '您有新的待办事项，请登录控制台查看。',
            'user' => '您有新的待办事项，请登录控制台查看。'
        ],
        'params' => [
            'template_id_short' => '52305',
            'keywords' => ['项目名称', '客户名称', '提醒时间']
        ]
    ];
}
