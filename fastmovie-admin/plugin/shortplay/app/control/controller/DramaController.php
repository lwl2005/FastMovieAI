<?php

namespace plugin\shortplay\app\control\controller;

use app\Basic;
use app\expose\build\builder\ActionBuilder;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\shortplay\app\model\PluginShortplayStyle;
use plugin\shortplay\utils\enum\StyleClassify;
use support\Request;

class DramaController extends Basic
{

    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder([
        ]);
        // $builder->addAction('操作', [
        //     'width' => '180px',
        //     'fixed' => 'right'
        // ]);
        // $builder->addTableAction('编辑', [
        //     'model' => Action::DIALOG['value'],
        //     'path' => '/app/shortplay/control/Actor/update',
        //     'props' => [
        //         'title' => '编辑《ID：{id}》演员'
        //     ],
        //     'component' => [
        //         'name' => 'button',
        //         'props' => [
        //             'type' => 'primary',
        //             'size' => 'small'
        //         ]
        //     ]
        // ]);
        $builder->addHeader();
        $formBuilder = new FormBuilder(null, null, [
            'inline' => true
        ]);
        $formBuilder->add('title', '名称', 'input', '', [
            'props' => [
                'placeholder' => '名称搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('uid', '用户ID', 'input', '', [
            'props' => [
                'placeholder' => '用户ID搜索',
                'clearable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('user', '用户', [
            'where' => [
                ['uid', '!=', null]
            ],
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'user.nickname',
                    'avatar' => 'user.headimg',
                    'info' => 'user.mobile',
                    'tags' => [
                        [
                            'field' => 'uid',
                            'props' => [
                                'type' => 'success',
                                'size' => 'small'
                            ]
                        ]
                    ],
                ]
            ],
            'props' => [
                'width' => '300px'
            ]
        ]);
        $builder->add('title', '剧本名称', [
            'props' => [
                'minWidth' => '200px'
            ]
        ]);
        $builder->add('description', '剧本简介', [
            'props' => [
                'minWidth' => '300px'
            ]
        ]);
        $builder->add('cover', '封面', [
            'component' => [
                'name' => 'image',
                'props' => [
                    'style' => 'width: 100px; height: 100px;',
                    'fit' => 'cover'
                ]
            ],
            'props' => [
                'width' => '133px'
            ]
        ]);
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'switch',
                'api' => '/app/shortplay/control/Drama/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('create_time', '时间', [
            'props' => [
                'width' => '200px'
            ],
            'component' => [
                'name' => 'table-times',
                'props' => [
                    'group' => [
                        [
                            'field' => 'create_time',
                            'label' => '创建'
                        ],
                        [
                            'field' => 'update_time',
                            'label' => '更新'
                        ]
                    ]
                ]
            ]
        ]);
        $builder = $builder->builder();
        return $this->resData($builder);
    }

    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $where[] = ['channels_uid', '=', $request->channels_uid];
        $title = $request->get('title');
        if ($title) {
            $where[] = ['title', 'like', "%{$title}%"];
        }
        $uid = $request->get('uid');
        if ($uid) {
            $where[] = ['uid', '=', $uid];
        }
        $list = PluginShortplayDrama::where($where)
            ->order('id desc')->paginate($limit);
        return $this->resData($list);
    }
}
