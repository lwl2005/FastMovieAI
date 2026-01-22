<?php

namespace plugin\article\app\admin\controller;

use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\Examine;
use app\expose\enum\State;
use plugin\article\app\Basic;
use plugin\article\app\model\PluginArticle;
use plugin\article\app\model\PluginArticleClassify;
use support\Request;
use think\facade\Db;

class ClassifyController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginArticleClassify();
    }
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder([
            'rowKey' => 'id',
            'api' => $this->plugin.'admin/Classify/index',
            'lazy' => true,
            'treeProps' => [
                'children' => 'children',
                'hasChildren' => 'hasChildren'
            ]
        ]);
        $builder->addAction('操作', [
            'width' => '160px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('编辑', [
            'path' => $this->plugin . 'admin/Classify/update',
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
            'path' => $this->plugin . 'admin/Classify/delete',
            'props' => [
                'type' => 'danger',
                'title' => '确定要删除《{title}》分类吗？'
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
        $builder->addHeaderAction('新建分类', [
            'path' => $this->plugin . 'admin/Classify/create',
            'props' => [
                'type' => 'success',
                'title' => '新建分类'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'success'
                ]
            ]
        ]);
        $formBuilder = new FormBuilder();
        $formBuilder->add('title', '标题', 'input', '', [
            'col' => [
                'xs' => 24,
                'sm' => 12,
                'md' => 8,
                'lg' => 6,
                'xl' => 4
            ],
            'props' => [
                'placeholder' => '标题搜索',
                'clearable' => true
            ]
        ]);

        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '80px'
            ]
        ]);
        $builder->add('title', '标题', [
            'props' => [
                'minWidth' => '200px'
            ]
        ]);
        $builder->add('alias', '别名', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'switch',
                'api' => $this->plugin . 'admin/Classify/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
            ],
            'props' => [
                'width' => '160px'
            ]
        ]);
        $builder->add('sort', '排序', [
            'component' => [
                'name' => 'input-number',
                'api' => $this->plugin . 'admin/Classify/indexUpdateField',
                'props' => [
                    'min' => 0,
                    'size'=>'small'
                ]
            ],
            'props' => [
                'width' => '200px'
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
        $id = $request->get('id');
        if ($id) {
            $where[] = ['pid', '=', $id];
        } else {
            $where[] = ['pid', '=', NULL];
        }
        $state = $request->get('state');
        if ($state) {
            $where[] = ['state', '=', $state];
        }
        $list = PluginArticleClassify::where($where)
            ->order('id desc')->paginate($limit)->each(function ($item) {
                $item->hasChildren = PluginArticleClassify::where(['pid' => $item->id])->count() > 0;
            });
        return $this->resData($list);
    }
    public function create(Request $request)
    {
        if ($request->method() == 'POST') {
            $D = $request->post();
            Db::startTrans();
            try {
                $PluginArticleClassify = new PluginArticleClassify;
                if($D['pid']){
                    $PluginArticleClassify->pid = $D['pid'];
                }
                $PluginArticleClassify->title = $D['title'];
                $PluginArticleClassify->alias = $D['alias'];
                $PluginArticleClassify->sort = $D['sort'];
                $PluginArticleClassify->state = $D['state'];
                $PluginArticleClassify->save();
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                return $this->fail($th->getMessage());
            }
            return $this->success('创建成功');
        }
        $builder = $this->getFormBuilder();
        return $this->resData($builder);
    }
    public function update(Request $request)
    {
        if ($request->method() == 'POST') {
            $D = $request->post();
            Db::startTrans();
            try {
                $PluginArticleClassify = PluginArticleClassify::where(['id' => $D['id']])->find();
                if($D['pid']){
                    $PluginArticleClassify->pid = $D['pid'];
                }else{
                    $PluginArticleClassify->pid = null;
                }
                $PluginArticleClassify->title = $D['title'];
                $PluginArticleClassify->alias = $D['alias'];
                $PluginArticleClassify->sort = $D['sort'];
                $PluginArticleClassify->state = $D['state'];
                $PluginArticleClassify->save();
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                return $this->fail($th->getMessage());
            }
            return $this->success('编辑成功');
        }
        $id = $request->get('id');
        $PluginArticleClassify = PluginArticleClassify::where(['id' => $id])->find();
        $builder = $this->getFormBuilder();
        $builder->setData($PluginArticleClassify->toArray());
        return $this->resData($builder);
    }
    public function getFormBuilder()
    {
        $builder = new FormBuilder(null,null,['size'=>'large']);
        $builder->add('pid', '分类等级', 'cascader', '', [
            'props' => [
                'options' => PluginArticleClassify::options(),
                'clearable' => true,
                'filterable' => true,
                'props' => [
                    'checkStrictly' => true,
                    'emitPath' => false
                ]
            ]
        ]);
        $builder->add('title', '标题', 'input', '', [
            'required' => true,
            'props' => [
                'maxlength' => 100,
                'show-word-limit' => true
            ]
        ]);
        $builder->add('alias', '别名', 'input', '', [
            'col'=>[
                'xs'=>24,
                'md'=>12
            ],
            'props' => [
                'maxlength' => 100,
                'show-word-limit' => true
            ]
        ]);
        $builder->add('sort', '排序', 'input-number', 99, [
            'required' => true,
            'col'=>[
                'xs'=>24,
                'md'=>12
            ],
            'props' => [
                'min' => 0,
                'controls'=>false
            ]
        ]);
        $builder->add('state', '状态', 'radio', State::YES['value'], [
            'required' => true,
            'options' => State::getOptions(),
            'subProps' => [
                'border' => true
            ]
        ]);
        return $builder;
    }
    public function delete(Request $request)
    {
        $id=$request->post('id');
        $PluginArticleClassify = PluginArticleClassify::where(['id' => $id])->find();
        if(!$PluginArticleClassify){
            return $this->fail('分类不存在');
        }
        if(PluginArticleClassify::where(['pid' => $id])->count()){
            return $this->fail('请先删除子分类');
        }
        if(PluginArticle::where(['classify_id' => $id])->count()){
            return $this->fail('请先删除该分类下的文章');
        }
        if($PluginArticleClassify->delete()){
            return $this->success('删除成功');
        }
        return $this->fail('删除失败');
    }
}
