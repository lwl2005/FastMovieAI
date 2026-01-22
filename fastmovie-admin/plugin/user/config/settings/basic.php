<?php

use app\expose\build\builder\ComponentBuilder;

$Component = new ComponentBuilder;
return [
    [
        'title' => '变更站点付费比例',
        'field' => 'site_change_fee_ratio',
        'value' => 50,
        'component' => 'input-number',
        #配置项额外属性
        'extra' => [
            #是否必填
            'required' => true,
            #提示
            'prompt' => [
                $Component->add('text', ['default' => '当用户变更站点授权域名和IP时，编辑次数不足，需要支付的站点费用比例，范围0-100，默认50'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'precision' => 0,
                'controls' => false
            ]
        ]
    ]
];