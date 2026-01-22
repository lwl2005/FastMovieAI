<?php

namespace plugin\marketing\app\control\controller;

use app\Basic;
use app\expose\build\builder\ActionBuilder;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use plugin\marketing\app\model\PluginMarketingPlan;
use plugin\marketing\app\model\PluginMarketingPlanPrice;
use plugin\marketing\utils\enum\PlanKey;
use plugin\marketing\utils\enum\BillingCycle;
use plugin\shortplay\utils\enum\StyleClassify;
use support\Request;

class PlanController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginMarketingPlan();
    }
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder;
        $builder->addHeader();
        $builder->addHeaderAction('创建会员套餐', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/marketing/control/Plan/create',
            'props' => [
                'title' => '创建会员套餐'
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
        $builder->addScreen($formBuilder);
        $builder->addAction('操作', [
            'width' => '220px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('编辑', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/marketing/control/Plan/update',
            'props' => [
                'title' => '编辑《ID：{id}》会员套餐'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'primary',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addTableAction('价格', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/marketing/control/Plan/priceList',
            'props' => [
                'title' => '会员套餐价格列表'
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
            'path' => '/app/marketing/control/Plan/delete',
            'where' => [
                ['is_system', '!=', 1]
            ],
            'props' => [
                'type' => 'error',
                'message' => '确定要删除吗？',
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
       
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('name', '名称', [
            'props' => [
                'width' => '150px'
            ]
        ]);
        $builder->add('description', '名称', [
            'props' => [
                'min-width' => '250px'
            ]
        ]);
        $builder->add('key', '套餐类型', [
            'component' => [
                'name' => 'tag',
                'options' => PlanKey::getOptions()
            ]
        ]);
        $builder->add('sort', '排序');
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'switch',
                'api' => '/app/marketing/control/Plan/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
            ],
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
        $where[] = ['channels_uid', '=', $request->channels_uid];
        $name = $request->get('name');
        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        $list = PluginMarketingPlan::where($where)
            ->order('id desc')->paginate($limit)->each(function ($item) {});
        return $this->resData($list);
    }
    public function create(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            try {
                $D['channels_uid'] = $request->channels_uid;
                $PluginMarketingPlan = new PluginMarketingPlan;
                $PluginMarketingPlan->save($D);
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
                $PluginMarketingPlan = PluginMarketingPlan::where(['id' => $D['id'], 'channels_uid' => $request->channels_uid])->find();
                $PluginMarketingPlan->save($D);
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            return $this->success('更新成功');
        }
        $id = $request->get('id');
        $Style = PluginMarketingPlan::where(['id' => $id, 'channels_uid' => $request->channels_uid])->find();
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
        $builder->add('name', '名称', 'input', '', [
            'required' => true,
            'maxlength' => 30,
            'show-word-limit' => true,
        ]);
        $builder->add('key', '套餐类型', 'radio', PlanKey::BASIC['value'], [
            'options' => PlanKey::getOptions(),
            'subProps' => [
                'border' => true
            ]
        ]);
    $component = new ComponentBuilder;
        $builder->add('description', '描述', 'input', '', [
            'required' => true,
            'props' => [
                'type' => 'textarea',
                'autosize' => [
                    'minRows' => 4,
                    'maxRows' => 20
                ],
                'placeholder' => '描述'
            ],
            'prompt' => [
                $component->add('text', ['default' => '描述使用 | 分割标签'], ['type' => 'info', 'size' => 'small'])->builder(),
            ]
        ]);
        $builder->add('sort', '排序', 'input-number', 0, [
            'required' => true,
            'min' => 0,
            'max' => 100,
        ]);
        $builder->add('state', '状态', 'radio', State::YES['value'], [
            'options' => State::getOptions(),
            'subProps' => [
                'border' => true
            ],
            'required' => true,
        ]);
        return $builder;
    }

    public function delete(Request $request)
    {
        $id = $request->get('id');
        $PluginMarketingPlan = PluginMarketingPlan::where(['id' => $id, 'channels_uid' => $request->channels_uid])->find();
        PluginMarketingPlanPrice::where(['plan_id' => $id])->delete();
        if (!$PluginMarketingPlan->delete()) {
            return $this->fail('删除失败');
        }
        return $this->success('删除成功');
    }


    public function priceListGetTable(Request $request)
    {
        $id = $request->get('id');
        $builder = new TableBuilder;
        $builder->addAction('操作', [
            'width' => '180px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('编辑', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/marketing/control/Plan/updatePrice',
            'props' => [
                'title' => '编辑《ID：{id}》会员套餐价格'
            ],
            'params' => [
                'id' => $id
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
        $builder->addHeaderAction('创建会员套餐价格', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/marketing/control/Plan/createPrice',
            'props' => [
                'title' => '创建会员套餐价格'
            ],
            'params' => [
                'plan_id' => $id
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'success'
                ]
            ]
        ]);

        $builder->addTableAction('删除', [
            'model' => Action::COMFIRM['value'],
            'path' => '/app/marketing/control/Plan/priceDelete',
            'where' => [
                ['is_system', '!=', 1]
            ],
            'props' => [
                'type' => 'error',
                'message' => '确定要删除吗？',
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
        $formBuilder = new FormBuilder(null, null, [
            'inline' => true
        ]);
        $formBuilder->add('billing_cycle', '计费周期', 'select', '', [
            'options' => BillingCycle::getOptions(),
            'props' => [
                'placeholder' => '计费周期搜索',
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('price', '价格', [
            'props' => [
                'width' => '150px'
            ]
        ]);
        $builder->add('original_price', '划线价', [
            'props' => [
                'width' => '150px'
            ]
        ]);
        $builder->add('points', '积分', [
            'props' => [
                'width' => '150px'
            ]
        ]);
        $builder->add('billing_cycle', '计费周期', [
            'component' => [        'name' => 'tag',
                'options' => BillingCycle::getOptions()
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

    public function priceList(Request $request)
    {
        $id = $request->get('id');
        $limit = $request->get('limit', 10);
        $where = [];
        $where[] = ['plan_id', '=', $id];
        $list = PluginMarketingPlanPrice::where($where)->order('id desc')->paginate($limit);
        return $this->resData($list);
    }

    public function createPrice(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            $PluginMarketingPlanPrice = new PluginMarketingPlanPrice;
            $PluginMarketingPlanPrice->save($D);
        }
        $builder = $this->getPriceFormBuilder();
        return $this->resData($builder);
    }
    public function updatePrice(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            $PluginMarketingPlanPrice = PluginMarketingPlanPrice::where(['id' => $D['id']])->find();
            $PluginMarketingPlanPrice->save($D);
            return $this->success('更新成功');
        }
        $id = $request->get('id');
        $PluginMarketingPlanPrice = PluginMarketingPlanPrice::where(['id' => $id])->find();
        if (!$PluginMarketingPlanPrice) {
            return $this->fail('会员套餐价格不存在');
        }
        $builder = $this->getPriceFormBuilder();
        $builder->setData($PluginMarketingPlanPrice->toArray());
        return $this->resData($builder);
    }
    private function getPriceFormBuilder()
    {
        $builder = new FormBuilder(null, null, [
            'translations' => true,
            'size' => 'large',
        ]);
        $builder->add('price', '价格', 'input-number', 0, [
            'required' => true,
            'min' => 0,
            'max' => 100,
            'props' => [
                'controls' => false
            ]
        ]);
        $builder->add('original_price', '划线价', 'input-number', 0, [
            'required' => true,
            'min' => 0,
            'max' => 100,
            'props' => [
                'controls' => false
            ]
        ]);
        $builder->add('points', '积分', 'input-number', 0, [
            'required' => true,
            'min' => 0,
            'max' => 100,
            'props' => [
                'controls' => false
            ]
        ]);
        $builder->add('billing_cycle', '计费周期', 'radio', BillingCycle::MONTH['value'], [
            'options' => BillingCycle::getOptions(),
            'subProps' => [
                'border' => true
            ]
        ]);
        return $builder;
    }

    public function priceDelete(Request $request)
    {
        $id = $request->post('id');
        $PluginMarketingPlanPrice = PluginMarketingPlanPrice::where(['id' => $id])->find();
        if (!$PluginMarketingPlanPrice) {
            return $this->fail('会员套餐价格不存在');
        }
        if ($PluginMarketingPlanPrice->delete()) {
            return $this->success('删除成功');
        }
        return $this->fail('失败');
    }
}
