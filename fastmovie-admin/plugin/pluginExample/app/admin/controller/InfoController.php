<?php

namespace plugin\pluginExample\app\admin\controller;

use app\Basic;
use app\expose\build\builder\InfoBuilder;
use support\Request;

class InfoController extends Basic
{
    public function index(Request $request)
    {
        $builder = new InfoBuilder();
        $builder->add('input', 'Input', 'span', [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 24,
                'lg' => 24,
                'xl' => 8,
            ],
            'props' => [
                'placeholder' => 'Input Placeholder',
                'clearable' => true
            ]
        ]);
        $subBuilder = new InfoBuilder('subInfo','Sub Info');
        $subBuilder->add('input', 'Input', 'span', [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 24,
                'lg' => 24,
                'xl' => 8,
            ],
            'props' => [
                'placeholder' => 'Input Placeholder',
                'clearable' => true
            ]
        ]);
        $builder->addGroupInfo($subBuilder);
        $builder->setData([
            'input' => 'Input Value',
            'subInfo' => [
                'input' => 'Sub Info Input Value'
            ]
        ]);
        return $this->resData($builder);
    }
}
