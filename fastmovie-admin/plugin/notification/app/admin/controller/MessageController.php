<?php

namespace plugin\notification\app\admin\controller;

use app\Basic;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use plugin\control\app\model\PluginChannelsUser;
use plugin\notification\app\model\PluginNotificationMessage;
use plugin\notification\expose\helper\Message;
use plugin\notification\utils\enum\MessageScene;
use support\Request;

class MessageController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginNotificationMessage();
    }

    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder();
        $builder->addHeader();
        $builder->addHeaderAction('推送测试', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/notification/admin/Message/pushTest',
            'props' => [
                'title' => '推送测试'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'success'
                ]
            ]
        ]);
        $channelList = PluginChannelsUser::options();
        $formBuilder = new FormBuilder(null, null, [
            'inline' => true
        ]);
        $formBuilder->add('channels_uid', '渠道', 'select', '', [
            'options' => $channelList,
            'props' => [
                'placeholder' => '渠道搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('uid', 'UID', 'input', '', [
            'props' => [
                'placeholder' => 'UID搜索',
            ]
        ]);
        $formBuilder->add('scene', '场景', 'select', '', [
            'options' => MessageScene::getOptions(),
            'props' => [
                'placeholder' => '场景搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('state', '状态', 'select', '', [
            'options' => State::getOptions(),
            'props' => [
                'placeholder' => '状态搜索',
                'clearable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        // $builder->addAction('操作', [
        //     'width' => '200px',
        //     'fixed' => 'right'
        // ]);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('channels', '渠道', [
            'where' => [
                ['channels_uid', '!=', null]
            ],
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'channels.nickname',
                    'avatar' => 'channels.headimg',
                    'info' => 'channels.mobile',
                    'tags' => [
                        [
                            'field' => 'channels_uid',
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
        $builder->add('title', '标题', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('scene', '场景', [
            'component' => [
                'name' => 'tag',
                'options' => MessageScene::getOptions()
            ],
            'props' => [
                'width' => '120px'
            ]
        ]);
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'switch',
                'api' => '/app/notification/admin/Message/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
            ],
            'props' => [
                'width' => '120px'
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
        $channels_uid = $request->get('channels_uid');
        if ($channels_uid) {
            $where[] = ['channels_uid', '=', $channels_uid];
        }
        $title = $request->get('title');
        if ($title) {
            $where[] = ['title', 'like', "%{$title}%"];
        }
        $scene = $request->get('scene');
        if ($scene) {
            $where[] = ['scene', '=', $scene];
        }
        $state = $request->get('state');
        if ($state) {
            $where[] = ['state', '=', $state];
        }
        $uid = $request->get('uid');
        if ($uid) {
            $where[] = ['uid', '=', $uid];
        }
        $list = PluginNotificationMessage::with(['user' => function ($query) {
            $query->field('id,nickname,headimg,mobile,channels_uid');
        }, 'channels' => function ($query) {
            $query->field('id,nickname,headimg,mobile');
        }])
            ->where($where)
            ->order('id desc')
            ->paginate($limit);
        return $this->resData($list);
    }

    public function pushTest(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            $Message = new Message();
            if ($D['channels_uid']) {
                $Message->setChannelsUid($D['channels_uid']);
            }
            if ($D['uid']) {
                $Message->setUid($D['uid']);
            }
            if ($D['scene']) {
                $Message->setScene($D['scene']);
            }
            if ($D['title']) {
                $Message->setTitle($D['title']);
            }
            if ($D['content']) {
                $Message->setContent($D['content']);
            }
            $Message->save();
            return $this->success('推送成功');
        }
        $formBuilder = new FormBuilder(null, null, [
            'labelPosition' => 'right',
            'label-width' => "200px",
            'class' => 'w-80 mx-auto',
            'size' => 'large'
        ]);
        $channelList = PluginChannelsUser::options();
        $formBuilder->add('channels_uid', '渠道', 'select', '', [
            'options' => $channelList,
            'props' => [
                'placeholder' => '渠道',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('uid', 'UID', 'input', '', [
            'props' => [
                'placeholder' => '推送指定UID',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('state', '状态', 'switch',  State::YES['value'], [
            'props' => [
                'active-value' => State::YES['value'],
                'inactive-value' => State::NO['value']
            ]
        ]);
        $formBuilder->add('scene', '场景', 'select', '', [
            'required' => true,
            'options' => MessageScene::getOptions()
        ]);
        $formBuilder->add('title', '标题', 'input', '', [
            'required' => true,
            'props' => [
                'placeholder' => '标题'
            ]
        ]);
        $formBuilder->add('content', '内容', 'input', '', [
            'required' => true,
            'props' => [
                'type' => 'textarea',
                'autosize' => [
                    'minRows' => 4,
                    'maxRows' => 20
                ],
                'placeholder' => '内容'
            ]
        ]);
        return $this->resData($formBuilder);
    }
}
