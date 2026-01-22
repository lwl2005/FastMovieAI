<?php

namespace plugin\finance\app\control\controller;

use app\Basic;
use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\EventName;
use app\expose\enum\PaymentChannels;
use app\expose\enum\PaymentCurrency;
use app\expose\enum\State;
use app\model\PaymentTemplate;
use plugin\apps\app\model\PluginAppsPrice;
use plugin\apps\app\model\PluginAppsSpecifications;
use plugin\apps\utils\enum\PaymentMethod;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\finance\app\model\PluginFinanceOrdersLog;
use plugin\finance\app\model\PluginFinanceWallet;
use plugin\finance\utils\enum\OrdersState;
use plugin\finance\utils\enum\OrdersType;
use plugin\marketing\app\model\PluginMarketingCoupon;
use plugin\marketing\app\model\PluginMarketingCouponCode;
use plugin\user\app\model\PluginUser;
use support\Log;
use support\Request;
use think\facade\Db;
use Webman\Event\Event;

class OrdersController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginFinanceOrders();
    }
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder();
        $builder->addAction('操作', [
            'width' => '160px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('完成', [
            'model' => Action::COMFIRM['value'],
            'path' => '/app/finance/control/Orders/finish',
            'props' => [
                'title' => '完成《{title}》订单'
            ],
            'where' => [
                ['state', '=', OrdersState::PAID['value']]
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'success',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addTableAction('实收', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/finance/control/Orders/real_money',
            'props' => [
                'title' => '实收《{title}》订单'
            ],
            'where' => [
                ['state', '=', OrdersState::WAIT_PAY['value']]
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'primary',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addTableAction('收付', [
            'model' => Action::DIALOG['value'],
            'path' => '/app/finance/control/Orders/receipt_money',
            'props' => [
                'title' => '确认已收到《{title}》订单对公转账'
            ],
            'where' => [
                ['state', '=', OrdersState::WAIT_VERIFIED['value']]
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'primary',
                    'size' => 'small'
                ]
            ]
        ]);
        $formBuilder = new FormBuilder(null, null, [
            'inline' => true
        ]);
        $formBuilder->add('title', '订单标题', 'input', '', [
            'props' => [
                'placeholder' => '订单标题搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('trade_no', '订单号', 'input', '', [
            'props' => [
                'placeholder' => '订单号搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('state', '状态', 'select', null, [
            'options' => OrdersState::getOptions(),
            'props' => [
                'placeholder' => '状态搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('type', '订单类型', 'select', null, [
            'options' => OrdersType::getOptions(),
            'props' => [
                'placeholder' => '订单类型搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('uid', '用户', 'select', null, [
            'options' => [],
            'remote' => [
                'url' => '/app/finance/control/Orders/queryUser',
            ],
            'props' => [
                'placeholder' => '用户搜索',
                'clearable' => true,
                'filterable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '80px'
            ]
        ]);
        $builder->add('order', '订单', [
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'title',
                    'info' => 'trade_no',
                ]
            ],
            'props' => [
                'minWidth' => '300px'
            ]
        ]);
        $builder->add('user', '用户', [
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
                'minWidth' => '300px'
            ]
        ]);
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'tag',
                'options' => OrdersState::getOptions()
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('type', '订单类型', [
            'component' => [
                'name' => 'tag',
                'options' => OrdersType::getOptions()
            ],
            'props' => [
                'width' => '140px'
            ]
        ]);
        $builder->add('statistic', '数据', [
            'component' => [
                'name' => 'statistic',
                'options' => [
                    [
                        'label' => '原价',
                        'value' => 'origin_money'
                    ],
                    [
                        'label' => '实付',
                        'value' => 'money'
                    ],
                    [
                        'label' => '数量',
                        'value' => 'num'
                    ]
                ]
            ],
            'props' => [
                'minWidth' => '380px'
            ]
        ]);
        $builder->add('coupon', '优惠券', [
            'component' => [
                'name' => 'tag',
            ],
            'props' => [
                'minWidth' => '300px'
            ]
        ]);
        $builder->add('create_time', '时间', [
            'props' => [
                'width' => '220px'
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
                            'field' => 'pay_time',
                            'label' => '支付'
                        ],
                        [
                            'field' => 'expire_time',
                            'label' => '过期'
                        ],
                        [
                            'field' => 'finish_time',
                            'label' => '完成'
                        ],
                        [
                            'field' => 'comment_time',
                            'label' => '评价'
                        ],
                        [
                            'field' => 'cancel_time',
                            'label' => '取消'
                        ]
                    ]
                ]
            ]
        ]);
        $builder = $builder->builder();
        return $this->resData($builder);
    }
    public function queryUser(Request $request)
    {
        $query = $request->post('query');
        if (empty($query)) {
            return $this->resData([]);
        }
        $where = [];
        $where[] = ['nickname|mobile', 'like', "%{$query}%"];
        $where[] = ['channels_uid', '=', $request->channels_uid];
        return $this->resData(PluginUser::options($where));
    }
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $where[] = ['channels_uid', '=', $request->channels_uid];
        $title = $request->get('title');
        if ($title) {
            $where[] = ['title', 'like', "%{$title}%"];
        }
        $trade_no = $request->get('trade_no');
        if ($trade_no) {
            $where[] = ['trade_no', '=', $trade_no];
        }
        $state = $request->get('state');
        if ($state !== null) {
            $where[] = ['state', '=', $state];
        }
        $type = $request->get('type');
        if ($type !== null) {
            $where[] = ['type', '=', $type];
        }
        $uid = $request->get('uid');
        if ($uid) {
            $where[] = ['uid', '=', $uid];
        }
        $list = PluginFinanceOrders::where($where)
            ->with(['user' => function ($query) {
                $query->field('id,nickname,headimg,mobile,channels_uid');
            }])
            ->order('id desc')->paginate($limit)->each(function ($item) {});
        return $this->resData($list);
    }
    public function finish(Request $request)
    {
        $id = $request->post('id');
        try {
            PluginFinanceOrders::finish(['id' => $id, 'channels_uid' => $request->channels_uid]);
        } catch (\Throwable $th) {
            return $this->exception($th);
        }
        return $this->success('完成订单成功');
    }
    public function real_money(Request $request)
    {
        if ($request->method() == 'POST') {
            $id = $request->post('id');
            $order = PluginFinanceOrders::where(['id' => $id, 'channels_uid' => $request->channels_uid])->find();
            if (!$order) {
                return $this->fail('订单不存在');
            }
            if ($order->state != OrdersState::WAIT_PAY['value']) {
                return $this->fail('订单状态不是待支付');
            }
            Db::startTrans();
            try {
                $order->state = OrdersState::PAID['value'];
                $order->pay_type = PaymentChannels::ADMIN['value'];
                $order->pay_time = date('Y-m-d H:i:s');
                $order->system_money = $request->post('system_money');
                $order->money = $order->origin_money - $order->system_money - $order->channels_money - $order->developer_money;
                $order->save();
                PluginFinanceOrdersLog::info($order, '管理员操作实收成功');
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                PluginFinanceOrdersLog::fail($order, $th->getMessage());
                return $this->exception($th);
            }
            Event::emit(EventName::ORDERS_PAY['value'], [
                'data' => $order,
                'payment_channel' => PaymentChannels::ADMIN['value'],
                'plugin' => 'finance'
            ]);
            try {
                PluginFinanceOrders::finish(['id' => $order->id, 'channels_uid' => $request->channels_uid]);
            } catch (\Throwable $th) {
                PluginFinanceOrdersLog::fail($order, $th->getMessage());
                Log::error("订单完成失败:{$order->trade_no},{$th->getMessage()},{$th->getFile()}:{$th->getLine()}", $th->getTrace());
                return $this->exception($th);
            }
            return $this->success('实收订单成功');
        }
        $id = $request->get('id');
        $order = PluginFinanceOrders::where(['id' => $id, 'channels_uid' => $request->channels_uid])->find();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->state != OrdersState::WAIT_PAY['value']) {
            return $this->fail('订单状态不是待支付');
        }
        $Component = new ComponentBuilder;
        $builder = new FormBuilder(null, null, [
            'translations' => true,
            'size' => 'large',
        ]);
        $builder->add('title', '订单', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 12,
                'xl' => 12
            ],
        ]);
        $builder->add('trade_no', '订单号', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 12,
                'xl' => 12
            ],
            'props' => [
                'type' => 'info',
                'size' => 'small'
            ]
        ]);
        $builder->add('create_time', '下单时间', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 12,
                'xl' => 12
            ],
            'props' => [
                'type' => 'info',
                'size' => 'small'
            ]
        ]);
        $builder->add('expire_time', '过期时间', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 12,
                'xl' => 12
            ],
            'props' => [
                'type' => 'info',
                'size' => 'small'
            ]
        ]);
        $builder->add('settle_accounts_time', '结算日期', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
        ]);
        $builder->add('num', '数量', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
        ]);
        $builder->add('origin_money', '原价', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
        ]);
        $builder->add('unit_money', '单价', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
        ]);
        $builder->add('money', '应付', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
            'props' => [
                'type' => 'danger'
            ]
        ]);
        $maxSystemMoney = $order->money;
        $builder->add('system_money', '平台优惠金额', 'input-number', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
            'required' => true,
            'props' => [
                'min' => 0,
                'max' => $maxSystemMoney,
                'step' => 0.01,
                'controls' => false
            ],
            'prompt' => [
                $Component->add('text', ['default' => '最大可优惠金额：' . $maxSystemMoney . '元'], ['type' => 'danger', 'size' => 'small'])->builder(),
            ],
            'children' => [
                'suffix' => [
                    'component' => 'el-text',
                    'props' => [
                        'size' => 'small'
                    ],
                    'children' => [
                        'default' => '元'
                    ]
                ],
                'prefix' => [
                    'component' => 'el-text',
                    'props' => [
                        'size' => 'small'
                    ],
                    'children' => [
                        'default' => '￥'
                    ]
                ]
            ]
        ]);
        $formula = 'money - system_money';
        $builder->add('real_money', '实收金额', 'compute', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
            'prompt' => [
                $Component->add('text', ['default' => '请确认财务已收到款项'], ['type' => 'info', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '实收后订单状态将变为：'], ['type' => 'info', 'size' => 'small'])->add('tag', ['default' => OrdersState::FINISH['label']], ['type' => OrdersState::FINISH['props']['type'], 'size' => 'small'])->builder(),
            ],
            'props' => [
                'formula' => $formula,
                'type' => 'danger',
                'size' => 'large',
                'tag' => 'b'
            ]
        ]);
        $builder->setData($order->toArray());
        return $this->resData($builder);
    }
    public function receipt_money(Request $request)
    {
        if ($request->method() == 'POST') {
            $id = $request->post('id');
            $order = PluginFinanceOrders::where(['id' => $id, 'channels_uid' => $request->channels_uid])->find();
            if (!$order) {
                return $this->fail('订单不存在');
            }
            if ($order->state != OrdersState::WAIT_VERIFIED['value']) {
                return $this->fail('订单状态不是待验证');
            }
            Db::startTrans();
            try {
                $order->state = OrdersState::PAID['value'];
                $order->pay_type = PaymentChannels::ADMIN['value'];
                $order->pay_time = date('Y-m-d H:i:s');
                $order->save();
                PluginFinanceOrdersLog::info($order, '管理员确认对公转账成功');
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                PluginFinanceOrdersLog::fail($order, $th->getMessage());
                return $this->exception($th);
            }
            Event::emit(EventName::ORDERS_PAY['value'], [
                'data' => $order,
                'payment_channel' => PaymentChannels::ADMIN['value'],
                'plugin' => 'finance'
            ]);
            try {
                PluginFinanceOrders::finish(['id' => $order->id, 'channels_uid' => $request->channels_uid]);
            } catch (\Throwable $th) {
                PluginFinanceOrdersLog::fail($order, $th->getMessage());
                Log::error("订单完成失败:{$order->trade_no},{$th->getMessage()},{$th->getFile()}:{$th->getLine()}", $th->getTrace());
                return $this->exception($th);
            }
            return $this->success('确认对公转账成功');
        }
        $id = $request->get('id');
        $order = PluginFinanceOrders::where(['id' => $id, 'channels_uid' => $request->channels_uid])->find();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->state != OrdersState::WAIT_VERIFIED['value']) {
            return $this->fail('订单状态不是待验证');
        }
        $Component = new ComponentBuilder;
        $builder = new FormBuilder(null, null, [
            'translations' => true,
            'size' => 'large',
        ]);
        $builder->add('title', '订单', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 12,
                'xl' => 12
            ],
        ]);
        $builder->add('trade_no', '订单号', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 12,
                'xl' => 12
            ],
            'props' => [
                'type' => 'info',
                'size' => 'small'
            ]
        ]);
        $builder->add('create_time', '下单时间', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 12,
                'xl' => 12
            ],
            'props' => [
                'type' => 'info',
                'size' => 'small'
            ]
        ]);
        $builder->add('expire_time', '过期时间', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 12,
                'xl' => 12
            ],
            'props' => [
                'type' => 'info',
                'size' => 'small'
            ]
        ]);
        $builder->add('num', '数量', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
        ]);
        $builder->add('origin_money', '原价', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
        ]);
        $builder->add('unit_money', '单价', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
        ]);
        $builder->add('money', '应付', 'text', null, [
            'col' => [
                'xs' => 24,
                'sm' => 24,
                'md' => 12,
                'lg' => 8,
                'xl' => 6
            ],
            'prompt' => [
                $Component->add('text', ['default' => '请确认已收到该订单的对公转账款项'], ['type' => 'warning', 'size' => 'small'])->builder(),
                $Component->add('text', ['default' => '确认后订单状态将变为：'], ['type' => 'info', 'size' => 'small'])->add('tag', ['default' => OrdersState::FINISH['label']], ['type' => OrdersState::FINISH['props']['type'], 'size' => 'small'])->builder(),
            ],
            'props' => [
                'type' => 'danger'
            ]
        ]);
        $builder->setData($order->toArray());
        return $this->resData($builder);
    }
}
