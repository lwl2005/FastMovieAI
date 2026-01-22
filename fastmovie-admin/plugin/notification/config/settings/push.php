<?php

use app\expose\build\builder\ComponentBuilder;

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
                $Component->add('text', ['default' => '是否开启站内推送'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => []
        ]
    ],
    [
        'title' => 'wss地址',
        'field' => 'wss_url',
        'value' => 'wss://{HOST}',
        'component' => 'input',
        'extra' => [
            'required' => true,
            'where' => [
                ['state', '=', true]
            ],
            'col'=>[
                'span'=>12
            ],
            'prompt' => [
                $Component->add('text', ['default' => '只需要填写wss://+域名，不需要填写路径'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'placeholder' => 'wss://+域名'
            ]
        ]
    ],
];
