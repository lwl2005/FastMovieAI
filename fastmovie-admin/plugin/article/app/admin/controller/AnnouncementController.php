<?php

namespace plugin\article\app\admin\controller;

use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\Examine;
use app\expose\enum\State;
use app\expose\helper\Config;
use plugin\article\app\Basic;
use plugin\article\app\model\PluginArticle;
use plugin\article\app\model\PluginArticleClassify;
use plugin\article\app\model\PluginArticleContent;
use plugin\article\app\validate\Article;
use support\Request;
use think\facade\Db;

class AnnouncementController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginArticle();
    }
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder();
        $builder->addAction('操作', [
            'width' => '220px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('编辑', [
            'path' => $this->plugin . 'admin/Announcement/update',
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'primary',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addTableAction('缓存', [
            'model' => Action::COMFIRM['value'],
            'path' => $this->plugin . 'admin/Announcement/cache',
            'props' => [
                'type' => 'warning',
                'title' => '确定要重新缓存《{title}》公告吗？'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'warning',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addTableAction('删除', [
            'model' => Action::COMFIRM['value'],
            'path' => $this->plugin . 'admin/Announcement/delete',
            'props' => [
                'type' => 'danger',
                'title' => '确定要删除《{title}》公告吗？'
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
        $builder->addHeaderAction('发布公告', [
            'path' => $this->plugin . 'admin/Announcement/create',
            'props' => [
                'type' => 'success',
                'title' => '发布公告'
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
        $formBuilder->add('state', '状态', 'select', '', [
            'col' => [
                'xs' => 24,
                'sm' => 12,
                'md' => 8,
                'lg' => 6,
                'xl' => 4
            ],
            'options' => State::getOptions(),
            'props' => [
                'placeholder' => '状态搜索',
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
            ],
            'component' => [
                'name' => 'table-times',
                'props' => [
                    'group' => [
                        [
                            'field' => 'title',
                            'label' => '标题',
                            'component' => 'text',
                            'props' => [
                                'type' => 'primary',
                                'size' => 'small'
                            ]
                        ],
                        [
                            'field' => 'subtitle',
                            'label' => '副标题',
                            'component' => 'text',
                            'props' => [
                                'type' => 'info',
                                'size' => 'small'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $builder->add('alias', '别名', [
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('view', '浏览量', [
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('source', '来源', [
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'switch',
                'api' => $this->plugin . 'admin/Announcement/indexUpdateState',
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
                'api' => $this->plugin . 'admin/Announcement/indexUpdateField',
                'props' => [
                    'min' => 0,
                    'size' => 'small'
                ]
            ],
            'props' => [
                'width' => '180px'
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
                        ],
                        [
                            'field' => 'release_time',
                            'label' => '发布'
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
            $where[] = ['a.title', 'like', "%{$title}%"];
        }
        $where[] = ['a.alias', '=', 'announcement'];
        $state = $request->get('state');
        if ($state) {
            $where[] = ['a.state', '=', $state];
        }
        $where[] = ['a.examine', '=', Examine::PASS['value']];
        $list = PluginArticle::alias('a')->where($where)
            ->join('plugin_article_classify c', 'c.id = a.classify_id', 'LEFT')
            ->field('a.*,c.title as classify_title')
            ->order('a.id desc')->paginate($limit)->each(function ($item) {});
        return $this->resData($list);
    }
    public function create(Request $request)
    {
        if ($request->method() == 'POST') {
            $D = $request->post();
            $validate = new Article;
            try {
                $validate->scene('create')->check($D);
            } catch (\Throwable $th) {
                return $this->fail($th->getMessage());
            }
            Db::startTrans();
            try {
                $PluginArticle = new PluginArticle;
                $PluginArticle->pid = $D['pid'];
                $PluginArticle->title = $D['title'];
                $PluginArticle->subtitle = $D['subtitle'];
                $PluginArticle->thumb = $D['thumb'];
                $PluginArticle->keywords = $D['keywords'];
                $PluginArticle->description = $D['description'];
                $PluginArticle->alias = 'announcement';
                $PluginArticle->view = $D['view'];
                $PluginArticle->sort = $D['sort'];
                $PluginArticle->source = $D['source'];
                $PluginArticle->state = $D['state'];
                $PluginArticle->examine = Examine::PASS['value'];
                if ($D['release_time']) {
                    $PluginArticle->release_time = $D['release_time'];
                } else {
                    $PluginArticle->release_time = date('Y-m-d H:i:s');
                }
                $PluginArticle->save();
                $PluginArticleContent = new PluginArticleContent;
                $PluginArticleContent->article_id = $PluginArticle->id;
                $PluginArticleContent->content = $D['content'];
                $PluginArticleContent->save();
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                return $this->fail($th->getMessage());
            }
            $this->cacheHtml($PluginArticle->id);
            return $this->success('创建成功');
        }
        $builder = $this->getFormBuilder();
        return $this->resData($builder);
    }
    public function update(Request $request)
    {
        if ($request->method() == 'POST') {
            $D = $request->post();
            $validate = new Article;
            try {
                $validate->scene('update')->check($D);
            } catch (\Throwable $th) {
                return $this->fail($th->getMessage());
            }
            Db::startTrans();
            try {
                $PluginArticle = PluginArticle::where(['id' => $D['id']])->find();
                $PluginArticle->pid = $D['pid'];
                $PluginArticle->title = $D['title'];
                $PluginArticle->subtitle = $D['subtitle'];
                $PluginArticle->thumb = $D['thumb'];
                $PluginArticle->keywords = $D['keywords'];
                $PluginArticle->description = $D['description'];
                $PluginArticle->view = $D['view'];
                $PluginArticle->sort = $D['sort'];
                $PluginArticle->source = $D['source'];
                $PluginArticle->state = $D['state'];
                $PluginArticle->release_time = $D['release_time'];
                $PluginArticle->save();
                $PluginArticleContent = PluginArticleContent::where(['article_id' => $D['id']])->find();
                $PluginArticleContent->content = $D['content'];
                $PluginArticleContent->save();
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                return $this->fail($th->getMessage());
            }
            return $this->success('编辑成功');
        }
        $id = $request->get('id');
        $PluginArticle = PluginArticle::alias('a')->where(['a.id' => $id])->join('plugin_article_content c', 'c.article_id=a.id')->field('a.*,c.content')->find();
        $builder = $this->getFormBuilder();
        $builder->setData($PluginArticle->toArray());
        return $this->resData($builder);
    }
    public function getFormBuilder()
    {
        $builder = new FormBuilder(null, null, ['size' => 'large']);
        $Component = new ComponentBuilder;
        $builder->add('classify_id', '所属分类', 'cascader', '', [
            'required' => true,
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
        $builder->add('subtitle', '副标题', 'input', '', [
            'props' => [
                'maxlength' => 200,
                'show-word-limit' => true
            ]
        ]);
        $builder->add('thumb', '封面', 'bundle', [], [
            'props' => [
                'accept' => 'image/*',
                'multiple' => 5
            ]
        ]);
        $builder->add('keywords', '关键词', 'input', '', [
            'props' => [
                'type' => 'textarea',
                'maxlength' => 200,
                'show-word-limit' => true,
                'autosize' => [
                    'minRows' => 2,
                    'maxRows' => 4
                ]
            ]
        ]);
        $builder->add('description', '描述', 'input', '', [
            'props' => [
                'type' => 'textarea',
                'maxlength' => 300,
                'show-word-limit' => true,
                'autosize' => [
                    'minRows' => 3,
                    'maxRows' => 6
                ]
            ]
        ]);
        $builder->add('alias', '别名', 'input', '', [
            'col' => [
                'xs' => 24,
                'md' => 12
            ],
            'prompt' => [
                $Component->add('text', ['default' => '设置别名后可通过别名访问公告'], ['type' => 'info', 'size' => 'small'])->builder()
            ],
            'props' => [
                'maxlength' => 30,
                'show-word-limit' => true
            ]
        ]);
        $builder->add('view', '浏览量', 'input-number', 0, [
            'col' => [
                'xs' => 24,
                'md' => 6
            ],
            'props' => [
                'min' => 0,
                'controls' => false
            ]
        ]);
        $builder->add('sort', '排序', 'input-number', 99, [
            'col' => [
                'xs' => 24,
                'md' => 6
            ],
            'props' => [
                'min' => 0,
                'controls' => false
            ]
        ]);
        $builder->add('source', '来源名称', 'input', '', [
            'prompt' => [
                $Component->add('text', ['default' => '为空则自动读取网站应用名称'], ['type' => 'info', 'size' => 'small'])->builder()
            ],
            'props' => [
                'maxlength' => 100,
                'show-word-limit' => true
            ]
        ]);
        $builder->add('state', '状态', 'radio', State::YES['value'], [
            'required' => true,
            'options' => State::getOptions(),
            'subProps' => [
                'border' => true
            ]
        ]);
        $builder->add('release_time', '发布时间', 'date-picker', null, [
            'prompt' => [
                $Component->add('text', ['default' => '为空则自动写入当前时间'], ['type' => 'info', 'size' => 'small'])->builder()
            ],
            'props' => [
                'type' => 'datetime',
                'format' => 'YYYY-MM-DD HH:mm:ss',
                'value-format' => 'YYYY-MM-DD HH:mm:ss',
                'placeholder' => '发布时间'
            ]
        ]);
        $builder->add('content', '内容', 'wangeditor', '', [
            'required' => true,
            'props' => [
                'class' => 'vh-60'
            ]
        ]);
        return $builder;
    }
    public function indexUpdateState(Request $request)
    {
        $id = $request->post('id');
        $field = $request->post('field');
        $value = $request->post('value');
        $model = $this->model->where(['id' => $id])->find();
        if (!$model) {
            return $this->fail('数据不存在');
        }
        $model->{$field} = $value;
        if ($model->save()) {
            try {
                if ($value === State::NO['value']) {
                    $this->clearHtml($id);
                } else {
                    $this->cacheHtml($id);
                }
            } catch (\Throwable $th) {
            }
            return $this->success();
        }
        return $this->fail('操作失败');
    }
    public function clearHtml($id)
    {
        $PluginArticle = PluginArticle::where(['id' => $id])->find();
        $path = public_path('article/' . $id . '.html');
        if (file_exists($path)) {
            unlink($path);
        }
        if ($PluginArticle->alias) {
            $path = public_path('article/' . $PluginArticle->alias . '.html');
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
    public function cacheHtml($id)
    {
        $PluginArticle = PluginArticle::alias('a')->where(['a.id' => $id])->join('plugin_article_content c', 'c.article_id=a.id')->field('a.*,c.content')->find();
        $config = new Config('basic', '');
        if (!$PluginArticle->source) {
            $PluginArticle->source = $config['web_name'];
        }
        if ($config['logo_use'] == 'image') {
            $PluginArticle->web_logo = $config['web_logo_light'];
        } else {
            $PluginArticle->web_logo = '/vite.svg';
        }
        $PluginArticle->web_title = "{$PluginArticle->title} - {$config['web_title']}";
        $html = view(base_path('plugin/article/public/template.html'), $PluginArticle->toArray(), null, '')->rawBody();
        $path = public_path('article/' . $id . '.html');
        file_put_contents($path, $html);
        if ($PluginArticle->alias) {
            $path = public_path('article/' . $PluginArticle->alias . '.html');
            file_put_contents($path, $html);
        }
    }
    public function cache(Request $request)
    {
        $id = $request->post('id');
        $this->cacheHtml($id);
        return $this->success('缓存成功');
    }
}
