<?php

namespace plugin\pluginExample\app\admin\controller;

use app\Basic;
use app\expose\build\builder\ExamineBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\enum\Examine;
use app\expose\enum\State;
use support\Request;

class ExamineController extends Basic
{
    public function index(Request $request)
    {
        $builder = new ExamineBuilder([
            'old'=>[
                'label'=>'原始数据',
                'type'=>'info'
            ],
            'new'=>[
                'label'=>'更新数据',
                'type'=>'success'
            ]
        ]);
        $builder->add('text', 'Text', 'text', [
            'props' => [
            ]
        ]);
        $builder->add('avatar', 'Avatar', 'avatar', [
            'props' => [
            ]
        ]);
        $builder->add('tag', 'Tag', 'tag', [
            'props' => [
            ],
            'options' => [
                [
                    'label' => '选项一',
                    'value' => '选项一'
                ],
                [
                    'label' => '选项二',
                    'value' => '选项二'
                ]
            ]
        ]);
        $builder->add('text', 'Text', 'text', [
            'props' => [
            ]
        ]);
        $builder->add('text', 'Text', 'text', [
            'props' => [
            ]
        ]);
        $builder->add('text', 'Text', 'text', [
            'props' => [
            ]
        ]);
        $builder->setOldData([
            'text'=>'文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字',
            'avatar'=>'头像',
            'tag'=>'选项一'
        ]);
        $builder->setNewData([
            'text'=>'文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字',
            'avatar'=>'头像',
            'tag'=>'选项一'
        ]);
        $formBuilder=new FormBuilder();
        $formBuilder->add('examine', '审核状态', 'radio', '', [
            'required' => true,
            'options' => Examine::getOptions(),
            'subProps' => [
                'border' => true
            ]
        ]);
        $formBuilder->add('remarks', '驳回理由', 'marked-editor', '', [
            'where' => [
                ['examine', '=', Examine::REJECT['value']]
            ],
            'required' => true,
            'props'=>[
                'bundle'=>true
            ]
        ]);
        $builder->addForm($formBuilder);
        return $this->resData($builder);
    }
}
