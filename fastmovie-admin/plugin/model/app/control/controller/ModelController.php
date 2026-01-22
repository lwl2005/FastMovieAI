<?php

namespace plugin\model\app\control\controller;

use app\Basic;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use app\expose\enum\Week;
use plugin\control\utils\yidevs\Yidevs;
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
        $builder->addAction('操作', [
            'width' => '200px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('编辑', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/model/control/Model/update',
            'props' => [
                'title' => '编辑《{name}》'
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
            'path' => '/app/model/control/Model/delete',
            'where' => [
                ['is_system', '!=', 1]
            ],
            'props' => [
                'type' => 'error',
                'message' => '确定要删除《{name}》吗？',
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
            'model' => Action::DIALOG['value'],
            'path' => '/app/model/control/Model/create',
            'props' => [
                'title' => '创建'
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
            'props' => [
                'width' => '140px'
            ]
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
                'name' => 'switch',
                'api' => '/app/model/control/Model/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
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
        $list = PluginModel::where($where)->order('id desc')->paginate($limit);
        return $this->resData($list);
    }


    public function create(Request $request)
    {
        if ($request->method() === 'POST') {
            $data = $request->post();
            $model_id = explode('_', $data['model'])[0];
            $model_name = explode('_', $data['model'])[1];
            $assistant_id = explode('_', $data['assistant'])[0];
            $assistant_name = explode('_', $data['assistant'])[1];
            unset($data['model'], $data['assistant']);
            $data['model_id'] = $model_id;
            $data['model_name'] = $model_name;
            $data['assistant_id'] = $assistant_id;
            $data['assistant_name'] = $assistant_name;
            $data['channels_uid'] = $request->channels_uid;
            $model = new PluginModel();
            if ($model->save($data)) {
                return $this->success('创建成功');
            }
            return $this->fail('创建失败');
        }
        $builder = $this->getFormBuilder($request);
        return $this->resData($builder);
    }


    public function update(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            $model = PluginModel::where(['id' => $D['id']])->find();
            $model_id = explode('_', $D['model'])[0];
            $model_name = explode('_', $D['model'])[1];
            $assistant_id = explode('_', $D['assistant'])[0];
            $assistant_name = explode('_', $D['assistant'])[1];
            unset($D['model'], $D['assistant']);
            $D['model_id'] = $model_id;
            $D['model_name'] = $model_name;
            $D['assistant_id'] = $assistant_id;
            $D['assistant_name'] = $assistant_name;
            if ($model->save($D)) {
                return $this->success('更新成功');
            }
            return $this->fail('更新失败');
        }
        $id = $request->get('id');
        $model = PluginModel::where(['id' => $id])->find();
        $model->model = $model->model_id . '_' . $model->model_name;
        $model->assistant = $model->assistant_id . '_' . $model->assistant_name;
        unset($model->model_id, $model->model_name, $model->assistant_id, $model->assistant_name);
        $builder = $this->getFormBuilder($request);
        $model->state = (int)$model->state;
        $builder->setData($model->toArray());
        return $this->resData($builder);
    }

    public function getFormBuilder(Request $request)
    {
        $formBuilder = new FormBuilder(null, null, [
            'labelPosition' => 'right',
            'label-width' => "200px",
            'class' => 'w-80 mx-auto',
            'size' => 'large'
        ]);
        $formBuilder->add('icon', '模型图标', 'bundle', '', [
            'required' => true,
            'props' => [
                'accept' => 'image/jpeg,image/png,image/jpg',
                'multiple' => 1,
                'size' => 2
            ]
        ]);
        $formBuilder->add('name', '模型名称', 'input', '', [
            'required' => true,
            'props' => [
                'placeholder' => '模型名称'
            ]
        ]);
        $formBuilder->add('model_type', '模型类别', 'radio', ModelType::CHAT['value'], [
            'required' => true,
            'options' => ModelType::getOptions(),
            'subProps' => [
                'border' => true
            ],
        ]);
        # 对话模型
        $ydModel = Yidevs::ChatModels($request->channels_uid);
        $modelList = $assistantList = [];
        foreach ($ydModel as $model) {
            $modelList[] = [
                'value' => $model['model_id'] . '_' . $model['name'],
                'tips' => $model['integral'] . $model['integral_unit'],
                'label' => $model['name']
            ];
        }
        $ydAssistant = Yidevs::ChatAssistantlist($request->channels_uid);
        foreach ($ydAssistant as $assistant) {
            $assistantList[] = [
                'value' => $assistant['assistant_id'] . '_' . $assistant['name'],
                'tips' => $assistant['integral'] . $assistant['integral_unit'],
                'label' => $assistant['name']
            ];
        }
        $formBuilder->add('model', '绑定模型', 'select', '', [
            'required' => true,
            'options' => $modelList,
            'where' => [
                ['model_type', '=', ModelType::CHAT['value']]
            ]
        ]);
        $formBuilder->add('assistant', '绑定助手', 'select', '', [
            'required' => true,
            'options' => $assistantList,
            'where' => [
                ['model_type', '=', ModelType::CHAT['value']]
            ]
        ]);
        # 绘图模型
        $ydModel = Yidevs::DrawModels($request->channels_uid);
        $modelList = $assistantList = [];
        foreach ($ydModel as $model) {
            $modelList[] = [
                'value' => $model['model_id'] . '_' . $model['name'],
                'tips' => $model['integral'] . $model['integral_unit'],
                'label' => $model['name']
            ];
        }
        $ydAssistant = Yidevs::DrawAssistantlist($request->channels_uid);
        foreach ($ydAssistant as $assistant) {
            $assistantList[] = [
                'value' => $assistant['assistant_id'] . '_' . $assistant['name'],
                'tips' => $assistant['integral'] . $assistant['integral_unit'],
                'label' => $assistant['name']
            ];
        }
        $formBuilder->add('model', '绑定模型', 'select', '', [
            'required' => true,
            'options' => $modelList,
            'where' => [
                ['model_type', '=', ModelType::DRAW['value']]
            ]
        ]);
        $formBuilder->add('assistant', '绑定助手', 'select', '', [
            'required' => true,
            'options' => $assistantList,
            'where' => [
                ['model_type', '=', ModelType::DRAW['value']]
            ]
        ]);
        # 视频模型
        $ydModel = Yidevs::VideoModels($request->channels_uid);
        $modelList = $assistantList = [];
        foreach ($ydModel as $model) {
            $modelList[] = [
                'value' => $model['model_id'] . '_' . $model['name'],
                'tips' => $model['integral'] . $model['integral_unit'],
                'label' => $model['name']
            ];
        }
        $ydAssistant = Yidevs::VideoAssistantlist($request->channels_uid);
        foreach ($ydAssistant as $assistant) {
            $assistantList[] = [
                'value' => $assistant['assistant_id'] . '_' . $assistant['name'],
                'tips' => $assistant['integral'] . $assistant['integral_unit'],
                'label' => $assistant['name']
            ];
        }
        $formBuilder->add('model', '绑定模型', 'select', '', [
            'required' => true,
            'options' => $modelList,
            'where' => [
                ['model_type', '=', ModelType::TOVIDEO['value']]
            ]
        ]);
        $formBuilder->add('assistant', '绑定助手', 'select', '', [
            'required' => true,
            'options' => $assistantList,
            'where' => [
                ['model_type', '=', ModelType::TOVIDEO['value']]
            ]
        ]);
        # 音频模型
        $ydModel = Yidevs::AudioModels($request->channels_uid);
        $modelList = $assistantList = [];
        foreach ($ydModel as $model) {
            $modelList[] = [
                'value' => $model['model_id'] . '_' . $model['name'],
                'tips' => $model['integral'] . $model['integral_unit'],
                'label' => $model['name']
            ];
        }
        $ydAssistant = Yidevs::AudioAssistantlist($request->channels_uid);
        foreach ($ydAssistant as $assistant) {
            $assistantList[] = [
                'value' => $assistant['assistant_id'] . '_' . $assistant['name'],
                'tips' => $assistant['integral'] . $assistant['integral_unit'],
                'label' => $assistant['name']
            ];
        }
        $formBuilder->add('model', '绑定模型', 'select', '', [
            'required' => true,
            'options' => $modelList,
            'where' => [
                ['model_type', '=', ModelType::AUDIO['value']]
            ]
        ]);
        $formBuilder->add('assistant', '绑定助手', 'select', '', [
            'required' => true,
            'options' => $assistantList,
            'where' => [
                ['model_type', '=', ModelType::AUDIO['value']]
            ]
        ]);

        $formBuilder->add('state', '状态', 'switch',  State::YES['value'], [
            'props' => [
                'active-value' => State::YES['value'],
                'inactive-value' => State::NO['value']
            ]
        ]);
        $formBuilder->add('point', '扣除数量', 'input-number', '', [
            'required' => true,
        ]);
        $formBuilder->add('scene', '场景', 'select', '', [
            'required' => true,
            'options' => ModelScene::getOptions()
        ]);
        $formBuilder->add('sort', '排序（小到大）', 'input-number', '', [
            'required' => true,
            'props' => [
                'placeholder' => '排序（小到大）'
            ]
        ]);
        $formBuilder->add('description', '描述', 'input', '', [
            'required' => true,
            'props' => [
                'type' => 'textarea',
                'autosize' => [
                    'minRows' => 4,
                    'maxRows' => 20
                ],
                'placeholder' => '描述'
            ]
        ]);
        return $formBuilder;
    }

    public function delete(Request $request)
    {
        $id = $request->post('id');
        $model = PluginModel::where(['id' => $id])->find();
        if ($model->delete()) {
            return $this->success('删除成功');
        }
        return $this->fail('删除失败');
    }
}
