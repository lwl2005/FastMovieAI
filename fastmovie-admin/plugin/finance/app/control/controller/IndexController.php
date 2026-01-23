<?php

namespace plugin\finance\app\control\controller;

use app\Basic;
use app\expose\build\builder\DataboardBuilder;
use app\expose\build\builder\databoardBuilder\component\Echarts;
use app\expose\build\builder\databoardBuilder\component\Statistic;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use app\expose\enum\Week;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\finance\utils\enum\OrdersState;
use support\Cache;
use support\Request;

class IndexController extends Basic
{
    public function indexGetTable(Request $request)
    {
        $builder = new TableBuilder();
        $builder->addAction('操作', [
            'width' => '200px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('编辑', [
            'path' => 'Control/update',
            'props' => [
                'type' => 'primary',
                'title' => '编辑《{nickname}》管理员'
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
            'path' => 'Control/delete',
            'where' => [
                ['is_system', '!=', 1]
            ],
            'props' => [
                'type' => 'error',
                'message' => '确定要删除《{nickname}》管理员吗？',
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
        $builder->addHeaderAction('创建管理员', [
            'path' => 'Control/create',
            'props' => [
                'type' => 'success',
                'title' => '创建管理员'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'success'
                ]
            ]
        ]);
        $formBuilder = new FormBuilder();
        $formBuilder->add('username', '账号', 'input', '', [
            'props' => [
                'placeholder' => '账号搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('mobile', '手机号', 'input', '', [
            'props' => [
                'placeholder' => '手机号搜索',
                'clearable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '80px'
            ]
        ]);
        $builder->add('userinfo', '管理员', [
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'nickname',
                    'avatar' => 'headimg',
                    'info' => 'username',
                    'nicknameTags' => [
                        [
                            'field' => 'new_text',
                            'props' => [
                                'type' => 'danger',
                                'size' => 'small'
                            ]
                        ],
                        [
                            'field' => 'new_text1',
                            'props' => [
                                'type' => 'success',
                                'size' => 'small'
                            ]
                        ]
                    ],
                    'tags' => [
                        [
                            'field' => 'role_name',
                            'props' => [
                                'type' => 'success'
                            ]
                        ]
                    ],
                ]
            ],
            'props' => [
                'minWidth' => '300px'
            ]
        ]);
        $builder->add('contact', '联系方式', [
            'props' => [
                'width' => '280px'
            ],
            'component' => [
                'name' => 'table-times',
                'props' => [
                    'group' => [
                        [
                            'field' => 'mobile',
                            'label' => '手机号'
                        ],
                        [
                            'field' => 'email',
                            'label' => '邮箱'
                        ],
                        [
                            'field' => 'wx_openid',
                            'label' => 'OpenID'
                        ]
                    ]
                ]
            ]
        ]);
        $builder->add('allow_week', '工作日', [
            'component' => [
                'name' => 'tag',
                'options' => Week::getOptions()
            ],
            'props' => [
                'width' => '240px'
            ]
        ]);
        $builder->add('allow_work', '工作时间', [
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
                            'type' => 'danger'
                        ]
                    ]
                ]
            ],
            'props' => [
                'width' => '200px'
            ]
        ]);
        $builder->add('online_time', '活动', [
            'props' => [
                'width' => '200px'
            ],
            'component' => [
                'name' => 'table-times',
                'props' => [
                    'group' => [
                        [
                            'field' => 'online_time',
                            'label' => '在线'
                        ],
                        [
                            'field' => 'login_time',
                            'label' => '登录'
                        ],
                        [
                            'component' => 'tag',
                            'field' => 'login_ip',
                            'label' => '登录IP',
                            'props' => [
                                'size' => 'small',
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'switch',
                'api' => 'Control/indexUpdateState',
                'props' => [
                    'active-value' => State::YES['value'],
                    'inactive-value' => State::NO['value']
                ]
            ],
            'props' => [
                'width' => '100px'
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
        $builder = new DataboardBuilder([
            'gutter' => 6
        ]);
       
        // 今日支付订单数
        $today = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereDay('create_time')->count();
        $yesterday = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereDay('create_time', 'yesterday')->count();
        $statistic = new Statistic;
        $statistic->setLabel('今日支付订单数')
            ->setUnit('笔')
            ->setData([
                'today' => $today,
                'yesterday' => $yesterday,
                'growth_rate' => $yesterday ? round(($today - $yesterday) / $yesterday * 100, 2) : 0
            ])
            ->setClass('p-6');
        $builder->add($statistic, [
            'xs' => 24,
            'sm' => 12,
            'md' => 6,
            'lg' => 4,
            'class' => 'bg-white p-4 rounded-4 shadow-lighter'
        ]);
        
        // 今日支付金额
        $today = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereDay('create_time')->sum('money');
        $yesterday = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereDay('create_time', 'yesterday')->sum('money');
        $statistic = new Statistic;
        $statistic->setLabel('今日支付金额')
            ->setUnit('元')
            ->setData([
                'today' => $today,
                'yesterday' => $yesterday,
                'growth_rate' => $yesterday ? round(($today - $yesterday) / $yesterday * 100, 2) : 0
            ])
            ->setClass('p-6');
        $builder->add($statistic, [
            'xs' => 24,
            'sm' => 12,
            'md' => 6,
            'lg' => 4,
            'class' => 'bg-white p-4 rounded-4 shadow-lighter'
        ]);
        
        // 总订单数
        $total = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->count();
        $lastMonthTotal = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereMonth('create_time', '-1 month')->count();
        $statistic = new Statistic;
        $statistic->setLabel('总订单数')
            ->setUnit('笔')
            ->setData([
                'today' => $total,
                'yesterday' => $lastMonthTotal,
                'growth_rate' => $lastMonthTotal ? round(($total - $lastMonthTotal) / $lastMonthTotal * 100, 2) : 0
            ])
            ->setClass('p-6');
        $builder->add($statistic, [
            'xs' => 24,
            'sm' => 12,
            'md' => 6,
            'lg' => 4,
            'class' => 'bg-white p-4 rounded-4 shadow-lighter'
        ]);
        
        // 当月订单数
        $month = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereMonth('create_time')->count();
        $lastMonth = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereMonth('create_time', '-1 month')->count();
        $statistic = new Statistic;
        $statistic->setLabel('当月订单数')
            ->setUnit('笔')
            ->setData([
                'today' => $month,
                'yesterday' => $lastMonth,
                'growth_rate' => $lastMonth ? round(($month - $lastMonth) / $lastMonth * 100, 2) : 0
            ])
            ->setClass('p-6');
        $builder->add($statistic, [
            'xs' => 24,
            'sm' => 12,
            'md' => 6,
            'lg' => 4,
            'class' => 'bg-white p-4 rounded-4 shadow-lighter'
        ]);
        
        // 添加图表
        $this->echarts($builder, $request);
    
        return $this->resData($builder);
    }
    
    /**
     * 添加图表组件
     * @param DataboardBuilder $builder
     * @param Request $request
     */
    private function echarts(DataboardBuilder $builder, Request $request)
    {
        $endTime = strtotime('23:59:59', time()) - time();
        $echarts = new Echarts;
        $echarts->setClass('bg-white p-4 rounded-4 shadow-lighter vh-65');
        $EchartsData = Cache::get('finance_echarts_data_' . $request->channels_uid);
        if (!$EchartsData) {
            $EchartsData = $this->getEchartsData($request);
            Cache::set('finance_echarts_data_' . $request->channels_uid, $EchartsData, $endTime);
        }
        $echarts->setData($EchartsData);
        $builder->add($echarts);
    }
    
    /**
     * 获取图表数据
     * @param Request $request
     * @return array
     */
    private function getEchartsData(Request $request)
    {
        $data = [
            'color' => ['#80FFA5', '#00DDFF', '#37A2FF', '#FF0087', '#FFBF00'],
            'title' => [
                'text' => '订单数据可视化'
            ],
            'tooltip' => [
                'trigger' => 'axis',
                'axisPointer' => [
                    'type' => 'cross',
                    'label' => [
                        'backgroundColor' => '#6a7985'
                    ]
                ]
            ],
            'legend' => [
                'data' => ["订单数", "支付金额"]
            ],
            'toolbox' => [
                'show' => false
            ],
            'grid' => [
                'left' => '3%',
                'right' => '4%',
                'bottom' => '3%',
                'containLabel' => true
            ],
            'xAxis' => [
                [
                    'type' => 'category',
                    'boundaryGap' => false,
                    'data' => []
                ]
            ],
            'yAxis' => [
                [
                    'type' => 'value'
                ]
            ],
            'series' => [
                [
                    'name' => "订单数",
                    'type' => 'line',
                    'smooth' => true,
                    'data' => []
                ],
                [
                    'name' => "支付金额",
                    'type' => 'line',
                    'smooth' => true,
                    'data' => []
                ]
            ]
        ];
        for ($i = 7; $i > 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i day"));
            $data['xAxis'][0]['data'][] = $date;
            $Order = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereDay('create_time', $date)->count();
            if ($Order) {
                $data['series'][0]['data'][] = $Order;
            } else {
                $data['series'][0]['data'][] = 0;
            }
            $Payment = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereDay('create_time', $date)->sum('money');
            if ($Payment) {
                $data['series'][1]['data'][] = $Payment;
            } else {
                $data['series'][1]['data'][] = 0;
            }
        }
        return $data;
    }
}
