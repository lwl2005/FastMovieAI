<?php

namespace plugin\user\app\control\controller;

use app\Basic;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use plugin\user\app\model\PluginUserInvitationCode;
use support\Request;

class InvitationCodeController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginUserInvitationCode();
    }

    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder();
        $builder->addHeader();
        $builder->addHeaderAction('生成邀请码', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/user/control/InvitationCode/create',
            'props' => [
                'title' => '生成邀请码'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'success'
                ]
            ]
        ]);
        $formBuilder = new FormBuilder(null, null, [
            'inline' => true
        ]);
        $formBuilder->add('code', '邀请码', 'input', '', [
            'props' => [
                'placeholder' => '邀请码搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('uid', '创建者', 'input', '', [
            'props' => [
                'placeholder' => '创建者搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('use_uid', '使用者', 'input', '', [
            'props' => [
                'placeholder' => '使用者搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('status', '状态', 'select', '', [
            'options' => [
                ['label' => '未使用', 'value' => 'unused'],
                ['label' => '已使用', 'value' => 'used']
            ],
            'props' => [
                'placeholder' => '状态搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('state', '启用状态', 'select', '', [
            'options' => State::getOptions(),
            'props' => [
                'placeholder' => '启用状态搜索',
                'clearable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
        ]);
        $builder->add('code', '邀请码', [
        ]);
        $builder->add('userinfo', '创建者', [
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'user.nickname',
                    'avatar' => 'user.headimg',
                    'info' => 'user.username',
                    'tags' => [
                        [
                            'field' => 'uid',
                            'props' => [
                                'type' => 'info',
                                'size' => 'small'
                            ]
                        ]
                    ]
                ]
            ],
            'props' => [
                'width' => '300px'
            ]
        ]);
        $builder->add('status', '使用状态', [
            'component' => [
                'name' => 'tag',
                'options' => [
                    [
                        'label' => '未使用',
                        'value' => 'unused',
                        'props' => [
                            'type' => 'info'
                        ]
                    ],
                    [
                        'label' => '已使用',
                        'value' => 'used',
                        'props' => [
                            'type' => 'success'
                        ]
                    ]
                ]
            ]
        ]);
        $builder->add('use_userinfo', '使用用户', [
            'props' => [
                'width' => '300px'
            ],
            'where' => [
                ['status', '=', 'used']
            ],
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'useUser.nickname',
                    'avatar' => 'useUser.headimg',
                    'info' => 'useUser.username',
                    'tags' => [
                        [
                            'field' => 'use_uid',
                            'props' => [
                                'type' => 'success',
                                'size' => 'small'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $builder->add('use_time', '使用时间', [
            'where' => [
                ['status', '=', 'used']
            ],
            'props' => [
                'width' => '180px'
            ]
        ]);
        $builder->add('state', '启用状态', [
            'component' => [
                'name' => 'switch',
                'api' => '/app/user/control/InvitationCode/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
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
                            'field' => 'use_time',
                            'label' => '使用'
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
        $uid = $request->get('uid');
        if ($uid) {
            $where[] = ['uid', '=', $uid];
        }
        $use_uid = $request->get('use_uid');
        if ($use_uid) {
            $where[] = ['use_uid', '=', $use_uid];
        }
        $code = $request->get('code');
        if ($code) {
            $where[] = ['code', 'like', "%{$code}%"];
        }

        $status = $request->get('status');
        if ($status) {
            $where[] = ['status', '=', $status];
        }

        $state = $request->get('state');
        if ($state !== null && $state !== '') {
            $where[] = ['state', '=', $state];
        }

        $list = PluginUserInvitationCode::alias('ic')
            ->where($where)
            ->with([
                'user' => function ($query) {
                    $query->field('id,nickname,headimg,username,channels_uid');
                },
                'useUser' => function ($query) {
                    $query->field('id,nickname,headimg,username,channels_uid');
                }
            ])
            ->order('ic.id desc')
            ->paginate($limit);

        return $this->resData($list);
    }

    public function create(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            $num = isset($D['num']) ? intval($D['num']) : 1;
            $uid = isset($D['uid']) ? intval($D['uid']) : 0;

            if ($num < 1 || $num > 100) {
                return $this->fail('生成数量必须在1-100之间');
            }

            if ($uid <= 0) {
                return $this->fail('请选择创建者用户');
            }

            try {
                PluginUserInvitationCode::addCode($uid, $num, $request->channels_uid);
                return $this->success('生成成功');
            } catch (\Throwable $th) {
                return $this->fail($th->getMessage());
            }
        }

        $builder = new FormBuilder(null, null, [
            'size' => 'large',
            'labelPosition' => 'right',
            'label-width' => '120px'
        ]);

        $Component = new ComponentBuilder;
        $builder->add('uid', '创建者', 'select', '', [
            'required' => true,
            'remote' => [
                'url' => '/app/user/control/User/query',
            ],
            'props' => [
                'clearable' => true,
                'filterable' => true,
                'remote' => true,
                'placeholder' => '请选择创建者用户'
            ]
        ]);
        $builder->add('num', '生成数量', 'input-number', 1, [
            'required' => true,
            'props' => [
                'min' => 1,
                'max' => 100,
                'controls' => true
            ],
            'prompt' => [
                $Component->add('text', ['default' => '一次最多生成100个邀请码'], ['type' => 'info', 'size' => 'small'])->builder()
            ]
        ]);

        return $this->resData($builder);
    }

    public function indexUpdateState(Request $request)
    {
        $id = $request->post('id');
        $field = $request->post('field');
        $value = $request->post('value');
        $model = $this->model->where(['id' => $id])->find();
        if (!$model) {
            return $this->fail('数据不存在');
        }
        $model->{$field} = $value;
        if ($model->save()) {
            return $this->success();
        }
        return $this->fail('操作失败');
    }
}
