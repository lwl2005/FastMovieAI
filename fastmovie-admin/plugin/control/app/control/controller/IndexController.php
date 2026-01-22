<?php

namespace plugin\control\app\control\controller;

use app\Basic;
use app\expose\build\builder\DataboardBuilder;
use app\expose\build\builder\databoardBuilder\component\Echarts;
use app\expose\build\builder\databoardBuilder\component\Statistic;
use app\expose\helper\Config;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\finance\utils\enum\OrdersState;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\user\app\model\PluginUser;
use support\Cache;
use support\Request;

class IndexController extends Basic
{
    /**
     * 不需要登录的方法
     * @var string[]
     */
    protected $notNeedLogin = ['index'];
    protected $notNeedAuth = ['index'];
    public function index(Request $request)
    {
        $path = $request->path();
        # 如果不是以/结尾的，就重定向到以/结尾的URL
        if (substr($path, -1) != '/') {
            return redirect($path . '/');
        }
        return view(public_path('index.html'));
    }
    public function control(Request $request)
    {
        $builder = new DataboardBuilder([
            'gutter' => 6
        ]);
        $today = PluginUser::where(['channels_uid' => $request->channels_uid])->whereDay('create_time')->count();
        $yesterday = PluginUser::where(['channels_uid' => $request->channels_uid])->whereDay('create_time', 'yesterday')->count();
        $statistic = new Statistic;
        $statistic->setLabel('今日新注册用户')
            ->setUnit('人')
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
        
        $today = PluginShortplayDrama::where(['channels_uid' => $request->channels_uid])->whereDay('create_time')->count();
        $yesterday = PluginShortplayDrama::where(['channels_uid' => $request->channels_uid])->whereDay('create_time', 'yesterday')->count();
        $statistic = new Statistic;
        $statistic->setLabel('今日短剧数')
            ->setUnit('部')
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
        
        $this->echarts($builder);
        return $this->resData($builder);
    }
    private function echarts(DataboardBuilder $builder)
    {
        $endTime = strtotime('23:59:59', time()) - time();
        $request = request();
        $echarts = new Echarts;
        $echarts->setClass('bg-white p-4 rounded-4 shadow-lighter vh-65');
        $EchartsData = Cache::get('control_echarts_data_' . $request->channels_uid);
        if (!$EchartsData) {
            $EchartsData = $this->getEchartsData($request);
            Cache::set('control_echarts_data_' . $request->channels_uid, $EchartsData, $endTime);
        }
        $echarts->setData($EchartsData);
        $builder->add($echarts);
    }
    private function getEchartsData(Request $request)
    {
        $data = [
            'color' => ['#80FFA5', '#00DDFF', '#37A2FF', '#FF0087', '#FFBF00'],
            'title' => [
                'text' => '数据可视化'
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
                'data' => ["用户", "订单数", "支付金额"]
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
                    'name' => "用户",
                    'type' => 'line',
                    'smooth' => true,
                    'data' => []
                ],
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
            $Statistic = PluginUser::where(['channels_uid' => $request->channels_uid])->whereDay('create_time', $date)->count();
            if ($Statistic) {
                $data['series'][0]['data'][] = $Statistic;
            } else {
                $data['series'][0]['data'][] = 0;
            }
            $Order = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereDay('create_time', $date)->count();
            if ($Order) {
                $data['series'][1]['data'][] = $Order;
            } else {
                $data['series'][1]['data'][] = 0;
            }
            $Payment = PluginFinanceOrders::where(['channels_uid' => $request->channels_uid])->whereIn('state', [OrdersState::PAID['value'], OrdersState::FINISH['value']])->whereDay('create_time', $date)->sum('money');
            if ($Payment) {
                $data['series'][2]['data'][] = $Payment;
            } else {
                $data['series'][2]['data'][] = 0;
            }
        }
        return $data;
    }
}
