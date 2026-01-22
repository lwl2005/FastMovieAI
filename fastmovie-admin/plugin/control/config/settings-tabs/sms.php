<?php

use app\expose\build\builder\ComponentBuilder;
use app\expose\enum\SmsChannels;
use plugin\user\utils\enum\VcodeScene;

$Component = new ComponentBuilder;
$vcode_templates=[];
$options=VcodeScene::getOptions();
foreach ($options as $key => $value) {
    $prompt=$Component->add("text", ["default" => "短信模板ID，变量："], ["type" => "info", "size" => "small"]);
    foreach ($value['variable'] as $k => $v) {
        $prompt->add("tag", ["default" => $v.'：'.$k], ["type" => "info", "size" => "small"]);
    }
    $vcode_templates[]=[
        'title' => $value['label'].'短信模板ID',
        'field' => 'vcode_template_'.$value['value'],
        'value' => '',
        'component' => 'input',
        'extra' => [
            'required' => true,
            'where' => [
                ['enable', '=', true]
            ],
            "prompt" => [
                $prompt->builder()
            ]
        ]
    ];
}
return [
    [
        'title' => '服务商',
        'field' => 'channels',
        'value' => [SmsChannels::ALIYUN['value'], SmsChannels::TENCENT['value'], SmsChannels::SMSBAO['value']],
        'component' => 'drag-sort',
        'extra' => [
            'required' => true,
            'prompt' => [
                $Component->add('text', ['default' => '短信服务商优先级，当发送失败时，会自动切换到下一个服务商'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'options' => SmsChannels::getOptions(),
        ]
    ],
    'group' => [
        [

            'title' => SmsChannels::ALIYUN['label'],
            'name' => SmsChannels::ALIYUN['value'],
            'children' => [
                [
                    'title' => '开关',
                    'field' => 'enable',
                    'value' => false,
                    'component' => 'switch',
                    'extra' => [
                        'prompt' => [
                            $Component->add('text', ['default' => '是否启用阿里云短信服务'], ['type' => 'info', 'size' => 'small'])
                                ->builder()
                        ]
                    ]
                ],
                [
                    'title' => 'AccessKey ID',
                    'field' => 'access_key_id',
                    'value' => '',
                    'component' => 'input',
                    'extra' => [
                        'required' => true,
                        'where' => [
                            ['enable', '=', true]
                        ],
                        'prompt' => [
                            $Component->add('text', ['default' => '阿里云-AccessKey管理-AccessKey ID，'], ['type' => 'info', 'size' => 'small'])
                                ->add('link', ['default' => '阿里云AccessKey'], ['target' => '_blank', 'href' => 'https://ram.console.aliyun.com/manage/ak', 'type' => 'info', 'underline' => 'never', 'size' => 'small'])
                                ->builder()
                        ]
                    ]
                ],
                [
                    'title' => 'AccessKey Secret',
                    'field' => 'access_secret',
                    'value' => '',
                    'component' => 'input',
                    'extra' => [
                        'required' => true,
                        'where' => [
                            ['enable', '=', true]
                        ],
                        'prompt' => [
                            $Component->add('text', ['default' => '阿里云-AccessKey管理-AccessKey Secret'], ['type' => 'info', 'size' => 'small'])
                                ->builder()
                        ]
                    ]
                ],
                [
                    'title' => '短信签名',
                    'field' => 'sign_name',
                    'value' => '',
                    'component' => 'input',
                    'extra' => [
                        'required' => true,
                        'where' => [
                            ['enable', '=', true]
                        ],
                        'prompt' => [
                            $Component->add('text', ['default' => '阿里云-短信服务-国内消息-签名管理，'], ['type' => 'info', 'size' => 'small'])
                                ->add('link', ['default' => '阿里云短信签名'], ['target' => '_blank', 'href' => 'https://dysms.console.aliyun.com/domestic/text', 'type' => 'info', 'underline' => 'never', 'size' => 'small'])
                                ->builder()
                        ]
                    ]
                ],
                ...$vcode_templates
            ]
        ],
        [
            "title" => SmsChannels::TENCENT['label'],
            "name" => SmsChannels::TENCENT['value'],
            "children" => [
                [
                    'title' => '开关',
                    'field' => 'enable',
                    'value' => false,
                    'component' => 'switch',
                    'extra' => [
                        'prompt' => [
                            $Component->add('text', ['default' => '是否启用腾讯云短信服务'], ['type' => 'info', 'size' => 'small'])
                                ->builder()
                        ]
                    ]
                ],
                [
                    "title" => "SecretId",
                    "field" => "secret_id",
                    "value" => "",
                    "component" => "input",
                    "extra" => [
                        "required" => true,
                        'where' => [
                            ['enable', '=', true]
                        ],
                        "prompt" => [
                            $Component->add("text", ["default" => "腾讯云-访问管理-访问密钥-AccessKey ID，"], ["type" => "info", "size" => "small"])
                                ->add("link", ["default" => "腾讯云SecretId"], ["target" => "_blank", "href" => "https://console.cloud.tencent.com/cam/capi", "type" => "info", 'underline' => 'never', "size" => "small"])
                                ->builder()
                        ]
                    ]
                ],
                [
                    "title" => "SecretKey",
                    "field" => "secret_key",
                    "value" => "",
                    "component" => "input",
                    "extra" => [
                        "required" => true,
                        'where' => [
                            ['enable', '=', true]
                        ],
                        "prompt" => [
                            $Component->add("text", ["default" => "腾讯云-访问管理-访问密钥-AccessKey Secret，"], ["type" => "info", "size" => "small"])
                                ->add("link", ["default" => "腾讯云SecretKey"], ["target" => "_blank", "href" => "https://console.cloud.tencent.com/cam/capi", "type" => "info", 'underline' => 'never', "size" => "small"])
                                ->builder()
                        ]
                    ]
                ],
                [
                    "title" => "应用 ID",
                    "field" => "appid",
                    "value" => "",
                    "component" => "input",
                    "extra" => [
                        "required" => true,
                        'where' => [
                            ['enable', '=', true]
                        ],
                        "prompt" => [
                            $Component->add("text", ["default" => "腾讯云-短信-应用管理-应用 ID，"], ["type" => "info", "size" => "small"])
                                ->add("link", ["default" => "腾讯云应用 ID"], ["target" => "_blank", "href" => "https://console.cloud.tencent.com/smsv2/app-manage", "type" => "info", 'underline' => 'never', "size" => "small"])
                                ->builder()
                        ]
                    ]
                ],
                [
                    "title" => "短信签名",
                    "field" => "sign_name",
                    "value" => "",
                    "component" => "input",
                    "extra" => [
                        "required" => true,
                        'where' => [
                            ['enable', '=', true]
                        ],
                        "prompt" => [
                            $Component->add("text", ["default" => "腾讯云-短信-国内短信-签名管理，"], ["type" => "info", "size" => "small"])
                                ->add("link", ["default" => "腾讯云短信签名"], ["target" => "_blank", "href" => "https://console.cloud.tencent.com/smsv2/csms-sign", "type" => "info", 'underline' => 'never', "size" => "small"])
                                ->builder()
                        ]
                    ]
                ],
                ...$vcode_templates
            ]
        ],
        [
            "title" => SmsChannels::SMSBAO['label'],
            "name" => SmsChannels::SMSBAO['value'],
            "children" => [
                [
                    'title' => '开关',
                    'field' => 'enable',
                    'value' => false,
                    'component' => 'switch',
                    'extra' => [
                        'prompt' => [
                            $Component->add('text', ['default' => '是否启用短信宝短信服务'], ['type' => 'info', 'size' => 'small'])
                                ->builder()
                        ]
                    ]
                ],
                [
                    'title' => 'UserName',
                    'field' => 'key',
                    'value' => '',
                    'component' => 'input',
                    'extra' => [
                        'required' => true,
                        'where' => [
                            ['enable', '=', true]
                        ],
                        'prompt' => [
                            $Component->add('text', ['default' => '短信宝-用户名'], ['type' => 'info', 'size' => 'small'])
                                ->builder()
                        ]
                    ]
                ],
                [
                    'title' => 'API Key或密码',
                    'field' => 'secret',
                    'value' => '',
                    'component' => 'input',
                    'extra' => [
                        'required' => true,
                        'where' => [
                            ['enable', '=', true]
                        ],
                        'prompt' => [
                            $Component->add('text', ['default' => '短信宝-API Key或密码'], ['type' => 'info', 'size' => 'small'])
                                ->builder()
                        ]
                    ]
                ],
                [
                    'title' => '短信签名',
                    'field' => 'sign_name',
                    'value' => '',
                    'component' => 'input',
                    'extra' => [
                        'required' => true,
                        'where' => [
                            ['enable', '=', true]
                        ],
                        'prompt' => [
                            $Component->add('text', ['default' => '短信宝-短信签名'], ['type' => 'info', 'size' => 'small'])
                                ->builder()
                        ]
                    ]
                ],
                ...$vcode_templates
            ]
        ]
    ],
];
