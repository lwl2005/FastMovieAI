<?php

namespace plugin\marketing\app\admin\controller;

use app\Basic;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use plugin\finance\utils\enum\OrdersType;
use plugin\marketing\app\model\PluginMarketingCoupon;
use plugin\marketing\app\model\PluginMarketingCouponApps;
use plugin\marketing\app\model\PluginMarketingCouponCode;
use plugin\marketing\app\model\PluginMarketingCouponExclusiveWith;
use plugin\marketing\app\model\PluginMarketingCouponPassword;
use plugin\marketing\app\model\PluginMarketingCouponServer;
use plugin\marketing\app\validate\PluginMarketingCoupon as ValidatePluginMarketingCoupon;
use plugin\marketing\utils\enum\CouponRule;
use plugin\marketing\utils\enum\ReceiveType;
use plugin\marketing\utils\enum\UseType;
use support\Request;
use think\facade\Db;

class CouponController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginMarketingCoupon();
    }
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder();
        $builder->addAction('操作', [
            'width' => '160px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('发行', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/marketing/admin/Coupon/release',
            'props' => [
                'title' => '发行《{title}》优惠券'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'success',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addTableAction('券码', [
            'model' => Action::REDIRECT['value'],
            'path' => '/app/marketing/admin/CouponCode/index',
            'field' => [
                'id' => 'coupon_id'
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
        $builder->addHeaderAction('创建优惠券', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/marketing/admin/Coupon/create',
            'props' => [
                'title' => '创建优惠券'
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
        $formBuilder->add('title', '券名', 'input', '', [
            'props' => [
                'placeholder' => '券名搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('state', '状态', 'select', null, [
            'options' => State::getOptions(),
            'props' => [
                'placeholder' => '状态搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('show_list', '列表显示', 'select', null, [
            'options' => State::getOptions(),
            'props' => [
                'placeholder' => '列表显示搜索',
                'clearable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '80px'
            ]
        ]);
        $expandBuilder = new TableBuilder([
            'size' => 'default',
        ]);
        $expandBuilder->add('password', '口令', [
            'component' => [
                'name' => 'copy'
            ]
        ]);
        $builder->add('password', '', [
            'components' => [
                [
                    'name' => 'table',
                    'col' => [
                        'xs' => 24,
                        'sm' => 12,
                        'md' => 6,
                        'lg' => 4,
                    ],
                    'builder' => $expandBuilder->builder()
                ]
            ],
            'props' => [
                'type' => 'expand'
            ]
        ]);
        $builder->add('title', '优惠券名', [
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'title',
                    'info' => 'code',
                ]
            ],
            'props' => [
                'width' => '180px'
            ]
        ]);
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'switch',
                'api' => '/app/marketing/admin/Coupon/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('show_list', '列表显示', [
            'component' => [
                'name' => 'switch',
                'api' => '/app/marketing/admin/Coupon/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('receive_use', '领取\使用规则', [
            'component' => [
                'name' => 'tag',
                'options' => [
                    [
                        'index' => 0,
                        'props' => [
                            'type' => 'success'
                        ]
                    ],
                    [
                        'index' => 1,
                        'props' => [
                            'type' => 'primary'
                        ]
                    ]
                ]
            ],
            'props' => [
                'width' => '160px'
            ]
        ]);
        $builder->add('discount_text', '优惠', [
            'component' => [
                'name' => 'tag'
            ],
            'props' => [
                'minWidth' => '180px'
            ]
        ]);
        $builder->add('statistic', '统计', [
            'component' => [
                'name' => 'statistic',
                'options' => [
                    [
                        'label' => '总数',
                        'value' => 'sum'
                    ],
                    [
                        'label' => '已使用',
                        'value' => 'use_num'
                    ],
                    [
                        'label' => '未领取',
                        'value' => 'num'
                    ],
                    [
                        'label' => '已领取',
                        'value' => 'receive_num'
                    ],
                    [
                        'label' => '已过期',
                        'value' => 'expire_num'
                    ]
                ]
            ],
            'props' => [
                'minWidth' => '380px'
            ]
        ]);
        $builder->add('stackable', '是否叠加', [
            'component' => [
                'name' => 'switch',
                'api' => '/app/marketing/admin/Coupon/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('server', '服务范围', [
            'component' => [
                'name' => 'tag',
            ],
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
        $title = $request->get('title');
        if ($title) {
            $where[] = ['title', 'like', "%{$title}%"];
        }
        $state = $request->get('state');
        if ($state !== null) {
            $where[] = ['state', '=', $state];
        }
        $show_list = $request->get('show_list');
        if ($show_list !== null) {
            $where[] = ['show_list', '=', $show_list];
        }
        $list = PluginMarketingCoupon::where($where)
            ->order('id desc')->paginate($limit)->each(function ($item) {
                $item->receive_use = [ReceiveType::getText($item->receive_type), UseType::getText($item->use_type)];
                $item->password = $item->password()->where(['coupon_id' => $item->id])->select();
                $serverTemp = $item->server()->column('server');
                $item->server = array_map(function ($value) {
                    return $value['label'];
                }, OrdersType::getOptions(function ($value) use ($serverTemp) {
                    return in_array($value['value'], $serverTemp);
                }));
            });
        return $this->resData($list);
    }

    /**
     * 创建优惠券品类
     */
    public function create(Request $request)
    {
        if ($request->method() === 'POST') {
            $post = $request->post();
            $validate = new ValidatePluginMarketingCoupon;
            try {
                $validate->check($post);
            } catch (\Throwable $th) {
                return $this->fail($th->getMessage());
            }
            Db::startTrans();
            try {
                $model = new PluginMarketingCoupon;
                $model->title = $post['title'];
                $model->coupon_rule = $post['coupon_rule'];
                switch ($post['coupon_rule']) {
                    case 'discount':
                        $model->discount = $post['discount'];
                        break;
                    case 'full_price':
                        $model->full_price = $post['full_price'];
                        $model->money = $post['money'];
                        break;
                }
                $model->state = $post['state'];
                $model->show_list = $post['show_list'];
                $model->receive_type = $post['receive_type'];
                $model->use_type = $post['use_type'];
                $model->stackable = $post['stackable'] ? 1 : 0;
                $model->save();
                foreach ($post['server'] as $item) {
                    $PluginMarketingCouponServer = new PluginMarketingCouponServer();
                    $PluginMarketingCouponServer->coupon_id = $model->id;
                    $PluginMarketingCouponServer->server = $item;
                    $PluginMarketingCouponServer->save();
                }
                $passwords = $post['passwords'];
                if (!empty($passwords)) {
                    $passwords = explode("\n", $passwords);
                    foreach ($passwords as $item) {
                        $item = trim($item);
                        if (empty($item)) {
                            continue;
                        }
                        if (PluginMarketingCouponPassword::where(['password' => $item])->count()) {
                            throw new \Exception("{$item}：优惠券口令重复");
                        }
                        $PluginMarketingCouponPassword = new PluginMarketingCouponPassword;
                        $PluginMarketingCouponPassword->coupon_id = $model->id;
                        $PluginMarketingCouponPassword->password = $item;
                        $PluginMarketingCouponPassword->save();
                    }
                }
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                return $this->exception($th);
            }
            return $this->success('创建成功');
        }
        $Component = new ComponentBuilder;
        $builder = new FormBuilder(null, null, [
            'labelPosition' => 'right',
            'label-width' => "200px",
            'class' => 'w-80 mx-auto',
            'size' => 'large'
        ]);
        $builder->add('title', '优惠券名', 'input', '', [
            'required' => true,
            'props' => [
                'placeholder' => '优惠券名'
            ]
        ]);
        $builder->add('passwords', '口令', 'input', '', [
            'prompt' => [
                $Component->add('text', ['default' => '每行一个口令，每个口令长度在50个字符以内。每个口令需保持全局唯一'], ['type' => 'info', 'size' => 'small'])->builder(),
            ],
            'props' => [
                'type' => "textarea",
                'placeholder' => '每行一个口令',
                'autosize' => [
                    'minRows' => 4,
                    'maxRows' => 20
                ]
            ]
        ]);
        $builder->add('coupon_rule', '优惠策略', 'radio', '', [
            'required' => true,
            'options' => CouponRule::getOptions(),
            'subProps' => [
                'border' => true
            ]
        ]);
        $builder->add('discount', '折扣', 'input-number', null, [
            'required' => true,
            'where' => [
                ['coupon_rule', '=', CouponRule::DISCOUNT['value']]
            ],
            'prompt' => [
                $Component->add('text', ['default' => '折扣说明：'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '1. 满小于等于0则为无限制'], ['type' => 'info', 'size' => 'small'])->builder(),
            ],
            'props' => [
                'step' => 0.01,
                'precision' => 2,
                'min' => 0,
                'max' => 1
            ]
        ]);
        $builder->add('full_price', '满减', 'input-number', null, [
            'required' => true,
            'where' => [
                ['coupon_rule', '=', CouponRule::FULL_PRICE['value']]
            ],
            'props' => [
                'precision' => 2,
                'min' => 0,
                'controls' => false
            ],
            'children' => [
                'prefix' => [
                    'component' => 'el-text',
                    'props' => [
                        'size' => 'small'
                    ],
                    'children' => [
                        'default' => '满'
                    ]
                ]
            ]
        ]);
        $builder->add('money', '满减', 'input-number', null, [
            'required' => true,
            'where' => [
                ['coupon_rule', '=', CouponRule::FULL_PRICE['value']]
            ],
            'props' => [
                'precision' => 2,
                'min' => 0,
                'controls' => false
            ],
            'prompt' => [
                $Component->add('text', ['default' => '折扣说明：'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '1. 0和1为不打折'], ['type' => 'info', 'size' => 'small'])->builder(),
            ],
            'children' => [
                'prefix' => [
                    'component' => 'el-text',
                    'props' => [
                        'size' => 'small'
                    ],
                    'children' => [
                        'default' => '减'
                    ]
                ]
            ]
        ]);
        $builder->add('state', '状态', 'radio', State::YES['value'], [
            'required' => true,
            'options' => State::getOptions(),
            'subProps' => [
                'border' => true
            ]
        ]);
        $builder->add('show_list', '列表中显示', 'radio', State::NO['value'], [
            'required' => true,
            'options' => [
                [
                    'label' => '显示',
                    'value' => State::YES['value']
                ],
                [
                    'label' => '隐藏',
                    'value' => State::NO['value']
                ]
            ],
            'prompt' => [
                $Component->add('text', ['default' => '是否在优惠广场列表中显示：'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '不可用：在优惠广场中，优惠券列表将不显示该优惠券'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '正常：在优惠广场中，优惠券列表显示可被所有用户领取'], ['type' => 'info', 'size' => 'small'])->builder(),
            ],
            'subProps' => [
                'border' => true
            ]
        ]);
        $builder->add('receive_type', '领取规则', 'radio', ReceiveType::REPEAT_DAY['value'], [
            'required' => true,
            'options' => ReceiveType::getOptions(),
            'prompt' => [
                $Component->add('text', ['default' => '领取规则：'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '账户唯一：该应用用户仅可领取一张，全平台生效'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '每日可领,每周可领,每月可领：用户可在规定时间内未领取则可领取一张'], ['type' => 'info', 'size' => 'small'])->builder(),
            ],
            'subProps' => [
                'border' => true
            ]
        ]);
        $builder->add('use_type', '使用规则', 'radio', UseType::UNLIMITED['value'], [
            'required' => true,
            'options' => UseType::getOptions(),
            'prompt' => [
                $Component->add('text', ['default' => '使用规则：'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '首单可用：新用户首单可用'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '应用首单可用：用户未购买过应用可用'], ['type' => 'info', 'size' => 'small'])->builder(),
            ],
            'subProps' => [
                'border' => true
            ]
        ]);
        $builder->add('server', '适用服务', 'checkbox', [], [
            'options' => OrdersType::getOptions(),
            'required' => true,
            'subProps' => [
                'border' => true
            ],
            'props' => [
                'min' => 1,
            ],
        ]);
        return $this->resData($builder);
    }
    public function release(Request $request)
    {
        if ($request->method() === 'POST') {
            $post = $request->post();
            if (empty($post['coupon_id'])) {
                return $this->fail('请选择优惠券');
            }
            $num = 1;
            if (!empty($post['num'])) {
                $num = (int)$post['num'];
                if ($num > 1000) {
                    $num = 1000;
                }
            }
            $start_time = null;
            $end_time = null;
            if (!empty($post['times'])) {
                $start_time = $post['times'][0];
                $end_time = $post['times'][1];
                if ($start_time > $end_time || $end_time < date('Y-m-d H:i:s')) {
                    return $this->fail('开始时间不能大于结束时间或结束时间不能小于当前时间');
                }
            }
            Db::startTrans();
            try {
                $prefix = 'A';
                $PluginMarketingCoupon = PluginMarketingCoupon::where(['id' => $post['coupon_id']])->find();
                $couponData = [];
                $createCode = [];
                for ($i = 0; $i < $num; $i++) {
                    $code = PluginMarketingCoupon::getCouponCode($prefix, $post['coupon_id']);
                    if (in_array($code, $createCode)) {
                        $i--;
                        continue;
                    }
                    $createCode[] = $code;
                    $couponData[] = [
                        'coupon_id' => $post['coupon_id'],
                        'coupon_rule' => $PluginMarketingCoupon->coupon_rule,
                        'coupon_code' => $code,
                        'plugin_id' => $PluginMarketingCoupon->plugin_id,
                        'discount' => $PluginMarketingCoupon->discount,
                        'full_price' => $PluginMarketingCoupon->full_price,
                        'money' => $PluginMarketingCoupon->money,
                        'receive_type' => $PluginMarketingCoupon->receive_type,
                        'start_time' => $start_time,
                        'end_time' => $end_time
                    ];
                }
                if (empty($couponData)) {
                    return $this->fail('创建失败');
                }
                $PluginMarketingCoupon->sum = Db::raw('sum+' . $num);
                $PluginMarketingCoupon->num = Db::raw('num+' . $num);
                $PluginMarketingCoupon->save();
                $PluginMarketingCouponCode = new PluginMarketingCouponCode();
                $PluginMarketingCouponCode->saveAll($couponData);
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                return $this->fail('发行失败：' . $th->getMessage());
            }
            return $this->success('发行成功');
        }
        $id = $request->get('id');
        $builder = new FormBuilder(null, null, [
            'labelPosition' => 'right',
            'label-width' => "200px",
            'class' => 'w-80 mx-auto',
            'size' => 'large',
            'submitButtonText' => '发行'
        ]);
        $Component = new ComponentBuilder;
        $builder->add('coupon_id', '优惠券', 'select', (int)$id, [
            'required' => true,
            'options' => PluginMarketingCoupon::options(),
            'props' => [
                'placeholder' => '请选择优惠券',
                'disabled' => true
            ]
        ]);
        $builder->add('times', '使用日期', 'date-picker', [], [
            'prompt' => [
                $Component->add('text', ['default' => '开始和结束日期说明：'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '1. 优惠券需在开始和结束日期之间才可使用'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '2. 未限制开始日期或未限制结束日期则优惠券随时可用'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '3. 如已过结束日期，系统将会清空未被领取的优惠券码'], ['type' => 'info', 'size' => 'small'])->builder()
            ],
            'props' => [
                'value-format' => "YYYY-MM-DD HH:mm:ss",
                'type' => "datetimerange",
                'start-placeholder' => "选择可使用开始日期",
                'end-placeholder' => "选择可使用结束日期"
            ]
        ]);
        $builder->add('day', '有效期', 'input-number', 30, [
            'required' => true,
            'props' => [
                'step' => 1,
                'precision' => 0,
                'min' => 1,
                'max' => 365
            ]
        ]);
        $builder->add('num', '数量', 'input-number', 1, [
            'required' => true,
            'prompt' => [
                $Component->add('text', ['default' => '数量说明：'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '1. 批量创建优惠券券码'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '2. 最低为1，最高单次为1000'], ['type' => 'info', 'size' => 'small'])->builder()

            ],
            'props' => [
                'step' => 1,
                'precision' => 0,
                'min' => 1,
                'max' => 1000
            ]
        ]);
        return $this->resData($builder);
    }
}
