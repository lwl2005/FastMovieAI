<?php

use app\expose\build\builder\ComponentBuilder;
use plugin\notification\utils\enum\Scene;

$data=[];
$Component = new ComponentBuilder;
$options=Scene::getOptions();
foreach ($options as $key => $value) {
    $temp=[
        'title' => $value['label'].'模板或模板ID',
        'field' => $value['value'],
        'value' => '',
        'component' => 'input',
        'extra' => [
            'prompt' => [
                $Component->add('text', ['default' => $value['label'].'场景使用的短信推送模板或模板ID，为空则关闭该场景通知'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ]
        ]
    ];
    $data[]=$temp;
}
return $data;
