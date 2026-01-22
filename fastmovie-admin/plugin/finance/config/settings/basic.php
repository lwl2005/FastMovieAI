<?php

use app\expose\build\builder\ComponentBuilder;

$Component = new ComponentBuilder;
return [
    [
        'title' => '订单过期时间',
        'field' => 'expire_time',
        'value' => 15,
        'component' => 'input-number',
        'extra' => [
            'required' => true,
            'prompt' => [
                $Component->add('text', ['default' => '订单过期时间，单位：分钟'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'min' => 1,
                'max' => 1440,
                'step' => 1,
                'precision' => 0,
                'controls' => false
            ]
        ]
    ],
];
