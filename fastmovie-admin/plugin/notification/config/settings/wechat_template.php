<?php

use app\expose\build\builder\ComponentBuilder;
use app\expose\enum\Action;
use plugin\notification\utils\enum\Scene;

$data=[];
$Component = new ComponentBuilder;
$options=Scene::getOptions();
foreach ($options as $key => $value) {
    $temp=[
        'title' => $value['label'].'模板ID',
        'field' => $value['value'],
        'value' => '',
        'component' => 'input',
        'extra' => [
            'prompt' => [
                $Component->add('text', ['default' => $value['label'].'场景使用的微信公众号推送模板ID，为空则关闭该场景通知'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [],
            'children'=>[
                'append'=>[
                    'component'=>'el-button',
                    'extra'=>[
                        'model'=>Action::COMFIRM['value'],
                        'path'=>'/app/notification/admin/WechatTemplate/getTemplateId',
                        'props'=>[
                            'type'=>'warning',
                            'message'=>'点击确定立即获取模板ID',
                        ]
                    ],
                    'children'=>[
                        'default'=>'一键获取模板ID'
                    ]
                ]
            ]
        ]
    ];
    if(isset($value['params'])){
        foreach ($value['params'] as $k => $v) {
            if(!isset($temp['extra']['children']['append']['extra']['params'])){
                $temp['extra']['children']['append']['extra']['params']=[];
            }
            $temp['extra']['children']['append']['extra']['params'][$k]=$v;
        }
    }
    $data[]=$temp;
}
return $data;
