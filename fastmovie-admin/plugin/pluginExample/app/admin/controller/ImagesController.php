<?php

namespace plugin\pluginExample\app\admin\controller;

use app\Basic;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\ImagesBuilder;
use app\expose\enum\Action;
use support\Request;

class ImagesController extends Basic
{
    public function indexGetGrid(Request $request)
    {
        $builder = new ImagesBuilder();
        $builder->addAction('编辑', [
            'path' => '/app/pluginExample/admin/Form/index',
            'props' => [
                'type' => 'primary',
                'title' => '编辑《{nickname}》'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'primary',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addAction('删除', [
            'model' => Action::COMFIRM['value'],
            'path' => '/app/pluginExample/admin/Table/delete',
            'where' => [
                ['is_system', '!=', 1]
            ],
            'props' => [
                'type' => 'error',
                'message' => '确定要删除《{nickname}》吗？',
                'confirmButtonClass' => 'el-button--danger'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'danger',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addHeader();
        $builder->addHeaderAction('创建', [
            'path' => '/app/pluginExample/admin/Form/create',
            'props' => [
                'type' => 'success',
                'title' => '创建'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'success'
                ]
            ]
        ]);
        $builder->addFooter();
        $builder->addFooterAction('移动到', [
            'model' => Action::DIALOG['value'],
            'path' => 'Admin/moveRole',
            'props' => [
                'title' => '移动到',
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'primary'
                ]
            ]
        ]);
        $formBuilder = new FormBuilder(null, null, [
            'inline' => true
        ]);
        $formBuilder->add('username', '账号', 'input', '', [
            'props' => [
                'placeholder' => '账号搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('mobile', '手机号', 'input', '', [
            'props' => [
                'placeholder' => '手机号搜索',
                'clearable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder = $builder->builder();
        return $this->resData($builder);
    }
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $data = [
            'total' => 100,
            'data' => []
        ];
        for ($i = 0; $i < $limit; $i++) {
            $data['data'][] = [
                'id' => $i,
                'title' => '图片' . $i,
                'url' => 'https://picsum.photos/1920/1080?random=' . $i,
            ];
        }
        return $this->resData($data);
    }
}
