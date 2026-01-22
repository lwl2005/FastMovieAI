<?php

namespace plugin\user\app\control\controller;

use app\Basic;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use plugin\user\app\model\PluginUser;
use plugin\user\expose\helper\User as HelperUser;
use support\Request;

class UserController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginUser;
    }
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder;
        $builder->addAction('操作', [
            'width' => '100px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('编辑', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/user/control/User/update',
            'props' => [
                'title' => '编辑《ID：{id}》用户'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'primary',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addHeader();
        $builder->addHeaderAction('创建用户', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/user/control/User/create',
            'props' => [
                'title' => '创建用户'
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
        $formBuilder->add('puid', '上级UID', 'input', '', [
            'props' => [
                'placeholder' => '上级UID搜索',
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
                'minWidth' => '300px'
            ]
        ]);
        $builder->add('puserinfo', '上级', [
            'where' => [
                ['puid', '!=', null]
            ],
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'parent.nickname',
                    'avatar' => 'parent,headimg',
                    'info' => 'parent.mobile',
                    'tags' => [
                        [
                            'field' => 'puid',
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
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'switch',
                'api' => '/app/user/control/User/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
            ],
            'props' => [
                'width' => '100px'
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
                            'field' => 'activation_time',
                            'label' => '激活'
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
    public function query(Request $request)
    {
        $query = $request->post('query');
        if (empty($query)) {
            return $this->resData([]);
        }
        $where = [];
        $where[] = ['nickname|username|mobile|email', 'like', "%{$query}%"];
        return $this->resData(PluginUser::options($where));
    }
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $where[] = ['channels_uid', '=', $request->channels_uid];
        $username = $request->get('username');
        if ($username) {
            $where[] = ['username', 'like', "%{$username}%"];
        }
        $mobile = $request->get('mobile');
        if ($mobile) {
            $where[] = ['mobile', 'like', "%{$mobile}%"];
        }
        $email = $request->get('email');
        if ($email) {
            $where[] = ['email', 'like', "%{$email}%"];
        }
        $puid = $request->get('puid');
        if ($puid) {
            $where[] = ['puid', '=', $puid];
        }
        $uid = $request->get('uid');
        if ($uid) {
            $where[] = ['id', '=', $uid];
        }
        $list = PluginUser::where($where)->with(['parent' => function ($query) {
            $query->field('id,nickname,headimg,mobile');
        }])
            ->order('id desc')->paginate($limit)->each(function ($item) {
                // 三天以内创建的
                $create_time = strtotime($item->create_time);
                $create_time = time() - $create_time;
                if ($create_time < 3 * 24 * 60 * 60) {
                    $item->new_text = '新用户';
                } elseif ($create_time < 7 * 24 * 60 * 60) {
                    $item->week_text = '一周内';
                } elseif ($create_time < 30 * 24 * 60 * 60) {
                    $item->month_text = '30天内';
                }
            });
        return $this->resData($list);
    }
    public function create(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            try {
                $D['channels_uid'] = $request->channels_uid;
                HelperUser::create($D);
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            return $this->success('创建成功');
        }
        $builder = $this->getFormBuilder();
        $Component = new ComponentBuilder;
        $builder->add('activation_time', '激活时间', 'date-picker', null, [
            'prompt' => [
                $Component->add('text', ['default' => '不选择时间则不激活，由用户前台登录后自动激活'], ['type' => 'info', 'size' => 'small'])
                    ->builder()
            ],
            'props' => [
                'placeholder' => '选择激活时间',
                'type' => 'datetime',
                'format' => 'YYYY-MM-DD HH:mm:ss',
                'value-format' => 'YYYY-MM-DD HH:mm:ss'
            ]
        ]);
        return $this->resData($builder);
    }
    public function update(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            try {
                $D['channels_uid'] = $request->channels_uid;
                HelperUser::update($D['id'], $D);
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            return $this->success('更新成功');
        }
        $id = $request->get('id');
        $User = PluginUser::where(['id' => $id])->withoutField('password')->find();
        $builder = $this->getFormBuilder();
        $builder->setData($User->toArray());
        return $this->resData($builder);
    }
    private function getFormBuilder()
    {
        $builder = new FormBuilder(null, null, [
            'labelPosition' => 'right',
            'label-width' => "200px",
            'class' => 'w-80 mx-auto',
            'size' => 'large',
        ]);
        $Component = new ComponentBuilder;
        $builder->add('nickname', '昵称', 'input', '', [
            'required' => true,
            'maxlength' => 30,
            'show-word-limit' => true
        ]);
        $builder->add('headimg', '头像', 'bundle', '', [
            'prompt' => [
                $Component->add('text', ['default' => '支持jpg、png、jpeg格式，大小不超过2M'], ['type' => 'info', 'size' => 'small'])->builder()
            ],
            'props' => [
                'accept' => 'image/jpeg,image/png,image/jpg',
                'multiple' => 1,
                'size' => 2
            ]
        ]);
        $builder->add('username', '账号', 'input', '', [
            'prompt' => [
                $Component->add('text', ['default' => '支持字母、数字、下划线，长度不超过30个字符'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '可用于账号登录'], ['type' => 'success', 'size' => 'small'])->builder()
            ],
            'props' => [
                'maxlength' => 30,
                'show-word-limit' => true
            ]
        ]);
        $builder->add('mobile', '手机号', 'input', '', [
            'prompt' => [
                $Component->add('text', ['default' => '中国大陆11位手机号'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '可用于账号，短信验证码登录'], ['type' => 'success', 'size' => 'small'])->builder()
            ],
            'props' => [
                'maxlength' => 11,
                'show-word-limit' => true
            ]
        ]);
        $builder->add('email', '邮箱', 'input', '', [
            'prompt' => [
                $Component->add('text', ['default' => '可用于账号，邮箱验证码登录'], ['type' => 'success', 'size' => 'small'])->builder()
            ],
            'props' => [
                'maxlength' => 100,
                'show-word-limit' => true
            ]
        ]);
        $builder->add('password', '密码', 'input', '', [
            'prompt' => [
                $Component->add('text', ['default' => '不修改密码请留空'], ['type' => 'info', 'size' => 'small'])->builder()
            ],
            'props' => [
                'placeholder' => '不修改密码请留空',
                'maxlength' => 30,
                'show-word-limit' => true
            ]
        ]);
        return $builder;
    }
}
