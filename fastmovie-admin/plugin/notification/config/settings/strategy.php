<?php

use app\expose\build\builder\ComponentBuilder;
use plugin\notification\utils\enum\Method;

$Component = new ComponentBuilder;
return [
    [
        'title' => '开关',
        'field' => 'state',
        'value' => false,
        'component' => 'switch',
        'extra' => [
            'required' => true,
            'prompt' => [
                $Component->add('text', ['default' => '是否开启通知'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => []
        ]
    ],
    [
        'title' => '同一用户每日通知次数',
        'field' => 'user_limit',
        'value' => 0,
        'component' => 'input-number',
        'extra' => [
            'required' => true,
            'where' => [
                ['state', '=', true]
            ],
            'col'=>[
                'xs' => 24,
                'sm' => 12,
                'md' => 6
            ],
            'prompt' => [
                $Component->add('text', ['default' => '同一用户所有场景每日通知次数，0：不限制'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'min' => 0,
                'controls'=>false,
                'placeholder' => '次数'
            ]
        ]
    ],
    [
        'title' => '同一用户同一场景每日通知次数',
        'field' => 'user_scene_limit',
        'value' => 1,
        'component' => 'input-number',
        'extra' => [
            'required' => true,
            'where' => [
                ['state', '=', true]
            ],
            'col'=>[
                'xs' => 24,
                'sm' => 12,
                'md' => 6
            ],
            'prompt' => [
                $Component->add('text', ['default' => '同一用户同一场景每日通知次数，0：不限制'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'min' => 0,
                'controls'=>false,
                'placeholder' => '次数'
            ]
        ]
    ],
    [
        'title' => '通知方式',
        'field' => 'method',
        'value' => Method::WECHAT_AND_SMS['value'],
        'component' => 'radio',
        'extra' => [
            'required' => true,
            'where' => [
                ['state', '=', true]
            ],
            'prompt' => [
                $Component->add('text', ['default' => '通知方式'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'options'=>Method::getOptions(),
            'subProps' => [
                'border'=>true
            ]
        ]
    ]
];
