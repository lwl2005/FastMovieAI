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
use plugin\shortplay\app\model\PluginShortplayStyle;
use plugin\shortplay\utils\enum\StyleClassify;
use support\Request;

class StyleController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginShortplayStyle;
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
        $formBuilder->add('classify', '风格分类', 'select', '', [
            'options' => StyleClassify::getOptions(),
            'required' => true,
            'props' => [
                'placeholder' => '风格分类搜索',
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
        $builder->add('classify', '风格分类', [
            'component' => [
                'name' => 'tag',
                'options' => StyleClassify::getOptions(),
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('styleinfo', '风格', [
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'name',
                    'avatar' => 'image',
                    'preview' => true
                ]
            ],
            'props' => [
                'width' => '300px'
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
        $builder->add('prompts', '提示词', [
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
        $where[] = ['name|prompts', 'like', "%{$query}%"];
        return $this->resData(PluginShortplayStyle::options($where));
    }
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $name = $request->get('name');
        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        $classify = $request->get('classify');
        if ($classify) {
            $where[] = ['classify', '=', $classify];
        }
        $channels_uid = $request->get('channels_uid');
        if ($channels_uid) {
            $where[] = ['channels_uid', '=', $channels_uid];
        }
        $list = PluginShortplayStyle::where($where)
            ->with(['channels' => function ($query) {
                $query->field('id,nickname,headimg,mobile');
            }])
            ->order('id desc')->paginate($limit)->each(function ($item) {});
        return $this->resData($list);
    }
}
