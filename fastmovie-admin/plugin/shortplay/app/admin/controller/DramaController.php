<?php

namespace plugin\shortplay\app\admin\controller;

use app\Basic;
use app\expose\build\builder\ActionBuilder;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use plugin\control\app\model\PluginChannelsUser;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\shortplay\app\model\PluginShortplayStyle;
use plugin\shortplay\utils\enum\StyleClassify;
use support\Request;

class DramaController extends Basic
{

    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder([]);
        $builder->addHeader();
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
                'name' => 'tag',
                'options' => State::getOptions(),
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
        $title = $request->get('title');
        if ($title) {
            $where[] = ['title', 'like', "%{$title}%"];
        }
        $uid = $request->get('uid');
        if ($uid) {
            $where[] = ['uid', '=', $uid];
        }
        $channels_uid = $request->get('channels_uid');
        if ($channels_uid) {
            $where[] = ['channels_uid', '=', $channels_uid];
        }
        $list = PluginShortplayDrama::where($where)
            ->with(['channels' => function ($query) {   
                $query->field('id,nickname,headimg,mobile');
            }])
            ->order('id desc')->paginate($limit);
        return $this->resData($list);
    }
}
