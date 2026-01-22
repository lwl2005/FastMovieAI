<?php

namespace plugin\user\app\control\controller;

use app\Basic;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use plugin\finance\utils\enum\PointsBillScene;
use plugin\user\app\model\PluginUser;
use plugin\user\app\model\PluginUserPointsBill;
use plugin\user\expose\helper\User as HelperUser;
use plugin\user\utils\enum\MoneyAction;
use support\Request;

class PointsController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginUserPointsBill();
    }
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder;
        $formBuilder = new FormBuilder(null, null, [
            'inline' => true
        ]);
        $formBuilder->add('nickname', '昵称', 'input', '', [
            'props' => [
                'placeholder' => '昵称搜索',
                'clearable' => true
            ]
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
        $formBuilder->add('email', '邮箱', 'input', '', [
            'props' => [
                'placeholder' => '邮箱搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('uid', 'UID', 'input', '', [
            'props' => [
                'placeholder' => 'UID搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('action', '变化类型', 'select', '', [
            'options' => MoneyAction::getOptions(),
            'props' => [
                'placeholder' => '变化类型搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('time', '时间', 'date-picker', '', [
            'props' => [
                'value-format' => "YYYY-MM-DD",
                'format' => "YYYY-MM-DD",
                'type' => 'daterange',
                'placeholder' => '时间范围搜索',
                'clearable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '100px'
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
                                'type' => 'success',
                                'size' => 'small'
                            ]
                        ],
                        [
                            'field' => 'week_text',
                            'props' => [
                                'type' => 'warning',
                                'size' => 'small'
                            ]
                        ],
                        [
                            'field' => 'month_text',
                            'props' => [
                                'type' => 'info',
                                'size' => 'small'
                            ]
                        ]
                    ]
                ]
            ],
            'props' => [
                'width' => '200px'
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
                        ]
                    ]
                ]
            ]
        ]);
        $builder->add('num', '变化金额', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('before', '变化前金额', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('after', '变化后金额', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('remarks', '备注', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('action', '变化类型', [
            'props' => [
                'width' => '120px'
            ],
            'component' => [
                'name' => 'tag',
                'options' => MoneyAction::getOptions()
            ]
        ]);
        $builder->add('scene', '触发场景', [
            'props' => [
                'width' => '120px'
            ],
            'component' => [
                'name' => 'tag',
                'options' => PointsBillScene::getOptions()
            ]
        ]);
        $builder->add('create_time', '创建时间', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder = $builder->builder();
        return $this->resData($builder);
    }
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $where[] = ['ub.channels_uid', '=', $request->channels_uid];
        $username = $request->get('username');
        if ($username) {
            $where[] = ['u.username', 'like', "%{$username}%"];
        }
        $mobile = $request->get('mobile');
        if ($mobile) {
            $where[] = ['u.mobile', 'like', "%{$mobile}%"];
        }
        $email = $request->get('email');
        if ($email) {
            $where[] = ['u.email', 'like', "%{$email}%"];
        }
        $uid = $request->get('uid');
        if ($uid) {
            $where[] = ['u.id', '=', $uid];
        }
        $time = $request->get('time');
        if ($time) {
            $where[] = ['ub.create_time', 'between', $time];
        }
        $list = PluginUserPointsBill::alias('ub')->join('plugin_user u', 'ub.uid = u.id')
            ->field('ub.*,u.nickname,u.headimg,u.username,u.mobile,u.email')
            ->where($where)
            ->order('ub.id desc')
            ->paginate($limit);
        return $this->resData($list);
    }
}
