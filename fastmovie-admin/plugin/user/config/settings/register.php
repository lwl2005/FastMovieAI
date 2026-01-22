<?php

use app\expose\build\builder\ComponentBuilder;

$Component = new ComponentBuilder;
return [
    [
        'title' => '注册邀请码数量',
        'field' => 'register_invitation_code_num',
        'value' => 4,
        'component' => 'input-number',
        'extra' => [
            'required' => true,
            'props' => [
                'min' => 0,
                'step' => 1,
                'precision' => 0,
                'controls' => false
            ]
        ]
    ],
    [
        'title' => '注册赠送积分',
        'field' => 'register_give_points',
        'value' => 100,
        'component' => 'input-number',
        'extra' => [
            'required' => true,
            'props' => [
                'min' => 0,
                'step' => 1,
                'precision' => 0,
                'controls' => false
            ]
        ]
    ],
    [
        'title' => '邀请奖励积分',
        'field' => 'invite_reward_points',
        'value' => 100,
        'component' => 'input-number',
        'extra' => [
            'required' => true,
            'props' => [
                'min' => 0,
                'step' => 1,
                'precision' => 0,
                'controls' => false
            ]
        ]
    ]
];
