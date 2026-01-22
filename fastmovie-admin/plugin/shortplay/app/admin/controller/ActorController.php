<?php

namespace plugin\shortplay\app\admin\controller;

use app\Basic;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use plugin\control\app\model\PluginChannelsUser;
use plugin\shortplay\app\model\PluginShortplayActor;
use plugin\shortplay\utils\enum\ActorAge;
use plugin\shortplay\utils\enum\ActorGender;
use plugin\shortplay\utils\enum\ActorSpeciesType;
use plugin\shortplay\utils\enum\ActorStatus;
use support\Request;

class ActorController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginShortplayActor;
    }
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder;
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
        $formBuilder->add('name', '名称', 'input', '', [
            'props' => [
                'placeholder' => '名称搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('actor_id', '演员ID', 'input', '', [
            'props' => [
                'placeholder' => '演员ID搜索',
                'clearable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('channels', '渠道', [
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'channels.nickname',
                    'avatar' => 'channels,headimg',
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
        $builder->add('actorinfo', '演员', [
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'name',
                    'avatar' => 'headimg',
                    'info' => 'actor_id',
                    'preview' => true
                ]
            ],
            'props' => [
                'width' => '300px'
            ]
        ]);
        $builder->add('three_view_image', '三视图', [
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
        $builder->add('species_type', '物种', [
            'component' => [
                'name' => 'tag',
                'options' => ActorSpeciesType::getOptions(),
            ],
            'props' => [
                'width' => '180px'
            ]
        ]);
        $builder->add('gender', '性别', [
            'component' => [
                'name' => 'tag',
                'options' => ActorGender::getOptions(),
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('age', '年龄', [
            'component' => [
                'name' => 'tag',
                'options' => ActorAge::getOptions(),
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('status', '状态', [
            'component' => [
                'name' => 'tag',
                'options' => ActorStatus::getOptions(),
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('remarks', '人物描述', [
            'props' => [
                'minWidth' => '300px'
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
        $where[] = ['name|actor_id|remarks', 'like', "%{$query}%"];
        return $this->resData(PluginShortplayActor::options($where));
    }
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $name = $request->get('name');
        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        $actor_id = $request->get('actor_id');
        if ($actor_id) {
            $where[] = ['actor_id', 'like', "%{$actor_id}%"];
        }
        $channels_uid = $request->get('channels_uid');
        if ($channels_uid) {
            $where[] = ['channels_uid', '=', $channels_uid];
        }
        $list = PluginShortplayActor::where($where)->with(['user' => function ($query) {
            $query->field('id,nickname,headimg,mobile,channels_uid');
        }, 'channels' => function ($query) {
            $query->field('id,nickname,headimg,mobile');
        }])
            ->order('id desc')->paginate($limit)->each(function ($item) {});
        return $this->resData($list);
    }
}
