<?php

namespace plugin\shortplay\app\control\controller;

use app\Basic;
use app\expose\build\builder\ActionBuilder;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
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
        $builder->addAction('操作', [
            'width' => '180px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('编辑', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/shortplay/control/Style/update',
            'props' => [
                'title' => '编辑《ID：{id}》风格'
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
        $builder->addHeaderAction('创建风格', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/shortplay/control/Style/create',
            'props' => [
                'title' => '创建风格'
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
                'name' => 'switch',
                'api' => '/app/shortplay/control/Style/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
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
        $where[] = ['channels_uid', '=', $request->channels_uid];
        $name = $request->get('name');
        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        $classify = $request->get('classify');
        if ($classify) {
            $where[] = ['classify', '=', $classify];
        }
        $list = PluginShortplayStyle::where($where)
            ->order('id desc')->paginate($limit)->each(function ($item) {});
        return $this->resData($list);
    }
    public function create(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            try {
                $PluginShortplayStyle = new PluginShortplayStyle;
                $PluginShortplayStyle->channels_uid = $request->channels_uid;
                $PluginShortplayStyle->name = $D['name'];
                $PluginShortplayStyle->classify = $D['classify'];
                $PluginShortplayStyle->image = $D['image'];
                $PluginShortplayStyle->state = $D['state'];
                $PluginShortplayStyle->prompts = $D['prompts'];
                $PluginShortplayStyle->save();
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            return $this->success('创建成功');
        }
        $builder = $this->getFormBuilder();
        return $this->resData($builder);
    }
    public function update(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            try {
                $PluginShortplayStyle = PluginShortplayStyle::where(['id' => $D['id'], 'channels_uid' => $request->channels_uid])->find();
                $PluginShortplayStyle->name = $D['name'];
                $PluginShortplayStyle->classify = $D['classify'];
                $PluginShortplayStyle->image = $D['image'];
                $PluginShortplayStyle->state = $D['state'];
                $PluginShortplayStyle->prompts = $D['prompts'];
                $PluginShortplayStyle->save();
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            return $this->success('更新成功');
        }
        $id = $request->get('id');
        $Style = PluginShortplayStyle::where(['id' => $id, 'channels_uid' => $request->channels_uid])->find();
        $builder = $this->getFormBuilder();
        $builder->setData($Style->toArray());
        return $this->resData($builder);
    }
    private function getFormBuilder()
    {
        $builder = new FormBuilder(null, null, [
            'translations' => true,
            'size' => 'large',
        ]);
        $Component = new ComponentBuilder;
        $builder->add('classify', '风格分类', 'radio', StyleClassify::ANIME['value'], [
            'options' => StyleClassify::getOptions(),
            'required' => true,
            'subProps' => [
                'border' => true
            ],
        ]);
        $builder->add('image', '风格图', 'bundle', '', [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 8
            ],
            'prompt' => [
                $Component->add('text', ['default' => '支持jpg、png、jpeg格式，大小不超过2M'], ['type' => 'info', 'size' => 'small'])->builder()
            ],
            'required' => true,
            'props' => [
                'accept' => 'image/jpeg,image/png,image/jpg',
                'multiple' => 1,
                'size' => 2,
                'customStyle' => '--el-upload-list-picture-card-height:200px;--el-upload-list-picture-card-width:160px;'
            ]
        ]);
        $actionBuilder = new ActionBuilder('', [
            'class' => 'mt-4'
        ]);
        $actionBuilder->add('AI 生成风格图', [
            'model' => Action::REQUEST['value'],
            'path' => '/app/shortplay/control/Style/generateImage',
            'field' => '*',
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'success',
                    'size' => 'small',
                    'bg' => true,
                    'text' => true,
                    'icon' => 'Refresh',
                ]
            ]
        ]);
        $builder->add('prompt', 'AI 提示词', 'input', '', [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 16,
                'xl' => 16
            ],
            'props' => [
                'type' => 'textarea',
                'autosize' => [
                    'minRows' => 4,
                    'maxRows' => 20
                ]
            ]
        ], $actionBuilder);
        $builder->add('name', '名称', 'input', '', [
            'required' => true,
            'maxlength' => 30,
            'show-word-limit' => true,
        ]);
        $builder->add('state', '状态', 'radio', State::YES['value'], [
            'options' => State::getOptions(),
            'subProps' => [
                'border' => true
            ],
            'required' => true,
        ]);
        $builder->add('prompts', '提示词', 'input', '', [
            'required' => true,
            'props' => [
                'type' => 'textarea',
                'autosize' => [
                    'minRows' => 4,
                    'maxRows' => 20
                ]
            ]
        ]);
        return $builder;
    }
    public function generateImage(Request $request)
    {
        $prompt = $request->post('prompt');
        if (empty($prompt)) {
            return $this->fail('AI提示词不能为空');
        }
        $actionBuilder = new ActionBuilder();
        $image = 'https://' . $request->host() . '/uploads/default/20251122/5b8dcf4cbec3ba58057884929194349c_69217f42205c5.jfif';
        $actionBuilder->setData([
            'image' => $image
        ]);
        $actionBuilder->setDataAction('append');
        return $this->success('生成成功', $actionBuilder);
    }
}
