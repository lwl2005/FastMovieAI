<?php

namespace plugin\shortplay\app\control\controller;

use app\Basic;
use app\expose\build\builder\ActionBuilder;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
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
        $builder->addAction('操作', [
            'width' => '180px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('编辑', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/shortplay/control/Actor/update',
            'props' => [
                'title' => '编辑《ID：{id}》演员'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'primary',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addTableAction('初始化', [
            'model' => Action::COMFIRM['value'],
            'path' => '/app/shortplay/control/Actor/submitInit',
            'props' => [
                'title' => '确定要初始化《{name}》演员吗？'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'warning',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addHeader();
        $builder->addHeaderAction('创建演员', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/shortplay/control/Actor/create',
            'props' => [
                'title' => '创建演员'
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
        $formBuilder->add('actor_id', '演员ID', 'input', '', [
            'props' => [
                'placeholder' => '演员ID搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('drama_id','公共演员','select','',[
            'options' => [
                ['label' => '是', 'value' => 1],
                ['label' => '否', 'value' => 0]
            ],
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '100px'
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
        $where[] = ['channels_uid', '=', $request->channels_uid];
        $name = $request->get('name');
        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        $actor_id = $request->get('actor_id');
        if ($actor_id) {
            $where[] = ['actor_id', 'like', "%{$actor_id}%"];
        }
        $drama_id = $request->get('drama_id');
        if ($drama_id==1) {
            $where[] = ['uid', '=', null];
        } else {
            $where[] = ['uid', '>', 0];
        }
        $list = PluginShortplayActor::where($where)->with(['user' => function ($query) {
            $query->field('id,nickname,headimg,mobile,channels_uid');
        }])
            ->order('id desc')->paginate($limit)->each(function ($item) {});
        return $this->resData($list);
    }
    public function create(Request $request)
    {
        if ($request->method() === 'POST') {
            $D = $request->post();
            try {
                $PluginShortplayActor = new PluginShortplayActor;
                $PluginShortplayActor->channels_uid = $request->channels_uid;
                $PluginShortplayActor->name = $D['name'];
                $PluginShortplayActor->headimg = $D['headimg'];
                $PluginShortplayActor->species_type = $D['species_type'];
                $PluginShortplayActor->gender = $D['gender'];
                $PluginShortplayActor->age = $D['age'];
                $PluginShortplayActor->status = $D['status'];
                $PluginShortplayActor->three_view_image = $D['three_view_image'];
                $PluginShortplayActor->remarks = $D['remarks'];
                $PluginShortplayActor->save();
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
                $PluginShortplayActor = PluginShortplayActor::where(['id' => $D['id'], 'channels_uid' => $request->channels_uid])->find();
                $PluginShortplayActor->channels_uid = $request->channels_uid;
                $PluginShortplayActor->name = $D['name'];
                $PluginShortplayActor->headimg = $D['headimg'];
                $PluginShortplayActor->species_type = $D['species_type'];
                $PluginShortplayActor->gender = $D['gender'];
                $PluginShortplayActor->age = $D['age'];
                $PluginShortplayActor->status = $D['status'];
                $PluginShortplayActor->three_view_image = $D['three_view_image'];
                $PluginShortplayActor->remarks = $D['remarks'];
                $PluginShortplayActor->save();
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            return $this->success('更新成功');
        }
        $id = $request->get('id');
        $Actor = PluginShortplayActor::where(['id' => $id, 'channels_uid' => $request->channels_uid])->find();
        $builder = $this->getFormBuilder();
        $builder->setData($Actor->toArray());
        return $this->resData($builder);
    }
    private function getFormBuilder()
    {
        $builder = new FormBuilder(null, null, [
            'translations' => true,
            'size' => 'large',
        ]);
        $Component = new ComponentBuilder;
        $builder->add('headimg', '形象', 'bundle', '', [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 8
            ],
            'prompt' => [
                $Component->add('text', ['default' => '支持jpg、png、jpeg格式，大小不超过3M'], ['type' => 'info', 'size' => 'small'])->builder()
            ],
            'props' => [
                'accept' => 'image/jpeg,image/png,image/jpg',
                'multiple' => 1,
                'size' => 3,
                'customStyle' => '--el-upload-list-picture-card-height:200px;--el-upload-list-picture-card-width:160px;'
            ]
        ]);
        $builder->add('three_view_image', '三视图', 'bundle', '', [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 8
            ],
            'prompt' => [
                $Component->add('text', ['default' => '支持jpg、png、jpeg格式，大小不超过3M，推荐1:1比例'], ['type' => 'info', 'size' => 'small'])->builder()
            ],
            'props' => [
                'accept' => 'image/jpeg,image/png,image/jpg',
                'multiple' => 1,
                'size' => 3,
                'customStyle' => '--el-upload-list-picture-card-height:200px;--el-upload-list-picture-card-width:200px;'
            ]
        ]);
        $builder->add('name', '名称', 'input', '', [
            'required' => true,
            'maxlength' => 30,
            'show-word-limit' => true,
        ]);
        $builder->add('species_type', '物种', 'select', '', [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 8
            ],
            'options' => ActorSpeciesType::getOptions(),
            'required' => true,
        ]);
        $builder->add('gender', '性别', 'select', '', [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 8
            ],
            'options' => ActorGender::getOptions(),
            'required' => true,
        ]);
        $builder->add('age', '年龄', 'select', '', [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 8
            ],
            'options' => ActorAge::getOptions(),
            'required' => true,
        ]);
        $builder->add('status', '状态', 'radio', ActorStatus::INITIALIZING['value'], [
            'options' => ActorStatus::getOptions(),
            'subProps' => [
                'border' => true
            ],
            'required' => true,
        ]);
        $builder->add('remarks', '人物描述', 'input', '', [
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
    public function generateHeadimg(Request $request)
    {
        $prompt = $request->post('prompt');
        if (empty($prompt)) {
            return $this->fail('AI提示词不能为空');
        }
        $actionBuilder = new ActionBuilder();
        $headimg = 'http://short-play.local.renloong.com/uploads/default/20251122/5b8dcf4cbec3ba58057884929194349c_69217f42205c5.jfif';
        $actionBuilder->setData([
            'headimg' => $headimg
        ]);
        $actionBuilder->setDataAction('append');
        return $this->success('生成成功', $actionBuilder);
    }
    public function generateThreeViewImage(Request $request)
    {
        $prompt = $request->post('prompt');
        if (empty($prompt)) {
            return $this->fail('AI提示词不能为空');
        }
        $actionBuilder = new ActionBuilder();
        $headimg = 'http://short-play.local.renloong.com/uploads/default/20251122/5b8dcf4cbec3ba58057884929194349c_69217f42205c5.jfif';
        $actionBuilder->setData([
            'three_view_image' => $headimg
        ]);
        $actionBuilder->setDataAction('append');
        return $this->success('生成成功', $actionBuilder);
    }
    public function submitInit(Request $request)
    {
        $id = $request->post('id');
        $Actor = PluginShortplayActor::where(['id' => $id, 'channels_uid' => $request->channels_uid])->find();
        if (!$Actor) {
            return $this->fail('演员不存在');
        }
        $Actor->status = ActorStatus::PENDING['value'];
        $Actor->actor_id = uniqid();
        $Actor->three_view_image = $Actor->headimg;
        $Actor->save();
        return $this->success('初始化成功');
    }
}
