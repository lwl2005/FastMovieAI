<?php

namespace plugin\pluginExample\app\admin\controller;

use app\Basic;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use app\expose\enum\Week;
use support\Request;

class TableController extends Basic
{
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder();
        $builder->addAction('操作', [
            'width' => '200px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('编辑', [
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
        $builder->addTableAction('删除', [
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
        $formBuilder = new FormBuilder(null,null,[
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
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '80px'
            ]
        ]);
        $builder->add('userinfo', '用户', [
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'nickname',
                    'avatar' => 'headimg',
                    'info' => 'username',
                    'nicknameTags' => [
                        [
                            'field' => 'new_text',
                            'props' => [
                                'type' => 'danger',
                                'size' => 'small'
                            ]
                        ],
                        [
                            'field' => 'new_text1',
                            'props' => [
                                'type' => 'success',
                                'size' => 'small'
                            ]
                        ]
                    ],
                    'tags' => [
                        [
                            'field' => 'role_name',
                            'props' => [
                                'type' => 'success'
                            ]
                        ]
                    ],
                ]
            ],
            'props' => [
                'minWidth' => '300px'
            ]
        ]);
        $builder->add('contact', '联系方式', [
            'props' => [
                'width' => '280px'
            ],
            'component' => [
                'name' => 'table-times',
                'props' => [
                    'group' => [
                        [
                            'field' => 'mobile',
                            'label' => '手机号'
                        ],
                        [
                            'field' => 'email',
                            'label' => '邮箱'
                        ],
                        [
                            'field' => 'wx_openid',
                            'label' => 'OpenID'
                        ]
                    ]
                ]
            ]
        ]);
        $builder->add('allow_week', '工作日', [
            'component' => [
                'name' => 'tag',
                'options' => Week::getOptions()
            ],
            'props' => [
                'width' => '240px'
            ]
        ]);
        $builder->add('allow_work', '工作时间', [
            'component' => [
                'name' => 'tag',
                'options' => [
                    [
                        'index' => 0,
                        'props' => [
                            'type' => 'success'
                        ]
                    ],
                    [
                        'index' => 1,
                        'props' => [
                            'type' => 'danger'
                        ]
                    ]
                ]
            ],
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('online_time', '活动', [
            'props' => [
                'width' => '200px'
            ],
            'component' => [
                'name' => 'table-times',
                'props' => [
                    'group' => [
                        [
                            'field' => 'online_time',
                            'label' => '在线'
                        ],
                        [
                            'field' => 'login_time',
                            'label' => '登录'
                        ],
                        [
                            'component' => 'tag',
                            'field' => 'login_ip',
                            'label' => '登录IP',
                            'props' => [
                                'size' => 'small',
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'switch',
                'api' => '/app/pluginExample/admin/Table/indexUpdateState',
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
        $data=[
            'total'=>1,
            'data'=>[
                [
                    'id' => 1,
                    'contact' => '13800138000',
                    'allow_week' => '周一',
                    'allow_work' => ['9:00','18:00'],
                    'online_time' => '2021-01-01 10:00:00',
                    'state' => 1,
                    'new_text' => '新',
                    'new_text1' => 'T',
                    'nickname' => '张三',
                    'headimg' => '',
                    'username' => 'admin',
                    'role_name' => '管理员',
                ]
            ]
        ];
        return $this->resData($data);
    }
}
