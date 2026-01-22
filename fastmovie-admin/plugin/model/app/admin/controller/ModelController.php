<?php

namespace plugin\model\app\admin\controller;

use app\Basic;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\State;
use plugin\control\app\model\PluginChannelsUser;
use plugin\model\app\model\PluginModel;
use plugin\model\utils\enum\ModelScene;
use plugin\model\utils\enum\ModelType;
use support\Request;

class ModelController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginModel();
    }
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder();
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
        $formBuilder->add('name', '模型名称', 'input', '', [
            'props' => [
                'placeholder' => '模型名称搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('model_type', '模型类型', 'select', '', [
            'options' => ModelType::getOptions(),
            'props' => [
                'placeholder' => '模型类型搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('model_name', '模型名称(壹定)', 'input', '', [
            'props' => [
                'placeholder' => '模型名称(壹定)搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('assistant_name', '助手名称', 'input', '', [
            'props' => [
                'placeholder' => '助手名称搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('scene', '场景', 'select', '', [
            'options' => ModelScene::getOptions(),
            'props' => [
                'placeholder' => '场景搜索',
                'clearable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '80px'
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
        $builder->add('icon', '模型图标', [
            'props' => [
                'width' => '100px'
            ],
            'component' => [
                'name' => 'image',
                'props' => [
                    'style' => 'width:40px;height:40px;'
                ]
            ]
        ]);
        $builder->add('name', '模型名称（本地）', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('model_type', '模型类型', [
            'component' => [
                'name' => 'tag',
                'options' => ModelType::getOptions()
            ],
        ]);
        $builder->add('model_name', '模型名称(壹定)', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('assistant_name', '助手名称', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('sort', '排序（小到大）', [
            'props' => [
                'width' => '130px'
            ]
        ]);
        $builder->add('point', '积分', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('scene', '场景', [
            'component' => [
                'name' => 'tag',
                'options' => ModelScene::getOptions()
            ],
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'tag',
                'options' => State::getOptions()
            ],
        ]);
        $builder->add('description', '描述', [
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
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $channels_uid = $request->get('channels_uid');
        if ($channels_uid) {
            $where[] = ['channels_uid', '=', $channels_uid];
        }
        $name = $request->get('name');
        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        $model_name = $request->get('model_name');
        if ($model_name) {
            $where[] = ['model_name', 'like', "%{$model_name}%"];
        }
        $assistant_name = $request->get('assistant_name');
        if ($assistant_name) {
            $where[] = ['assistant_name', 'like', "%{$assistant_name}%"];
        }
        $scene = $request->get('scene');
        if ($scene) {
            $where[] = ['scene', '=', $scene];
        }
        $model_type = $request->get('model_type');
        if ($model_type) {
            $where[] = ['model_type', '=', $model_type];
        }
        $list = PluginModel::where($where)->with(['channels' => function ($query) {
            $query->field('id,nickname,headimg,mobile');
        }])->order('id desc')->paginate($limit);
        return $this->resData($list);
    }
}
