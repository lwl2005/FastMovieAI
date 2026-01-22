<?php

use app\expose\build\builder\ComponentBuilder;

$Component = new ComponentBuilder;
return [
    [
        'title' => '壹定开放平台密钥',
        'field' => 'token',
        'value' => '',
        'component' => 'input',
        'extra' => [
            'required' => true,
            'prompt' => [
                $Component->add('text', ['default' => '壹定开放平台密钥，用于获取壹定开放平台数据。获取方式：在壹定开放平台->API服务->'], ['type' => 'info', 'size' => 'small'])
                    ->add('link', ['default' => '密钥'], ['href'=>'https://api.yidevs.com/?icode=V7hN2xiudv5qeISzYiklLFFTMElweG44dmwzTEp4K3Mwa3ZGelE9PQ%3D%3D','type' => 'primary', 'size' => 'small', 'target' => '_blank', 'underline' => 'never'])
                    ->builder()
            ],
            'props' => [
                'type' => 'text',
                'placeholder' => '请输入壹定开放平台密钥'
            ]
        ]
    ],
];
