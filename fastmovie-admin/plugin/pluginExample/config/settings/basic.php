<?php

use app\expose\build\builder\ComponentBuilder;

$Component = new ComponentBuilder;
return [
    [
        #配置项名称
        'title' => '配置项名称',
        #配置项字段
        'field' => 'web_name',
        #配置项值
        'value' => 'RenLoong',
        #配置项组件
        'component' => 'input',
        #配置项额外属性
        'extra' => [
            #是否必填
            'required' => true,
            #element-plus表单中的rules规则
            'rules' => [
                ['type'=>'string','required'=>true,'message'=>'请输入配置项值']
            ],
            #使用接口验证
            'rules_api' => [
                'url' => '/app/pluginExample/example/test'
            ],
            #提示
            'prompt' => [
                $Component->add('text', ['default' => '提示信息'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            #组件属性
            'props' => [
                'type' => 'text',
                'placeholder' => '请输入应用名称'
            ]
        ]
    ]
];