<?php

namespace plugin\pluginExample\app\admin\controller;

use app\Basic;
use app\expose\build\builder\DataboardBuilder;
use app\expose\build\builder\databoardBuilder\component\Domain;
use app\expose\build\builder\databoardBuilder\component\Echarts;
use app\expose\build\builder\databoardBuilder\component\Statistic;
use app\expose\enum\Action;
use support\Request;

class DataboardController extends Basic
{
    public function index(Request $request)
    {
        $builder = new DataboardBuilder([
            'gutter' => 6
        ]);
        $today=100;
        $yesterday=124;
        $statistic = new Statistic;
        $statistic->setLabel('今日新注册用户')
            ->setUnit('人')
            ->setData([
                'today' => $today,
                'yesterday' => $yesterday,
                'growth_rate' => $yesterday?round(($today-$yesterday)/$yesterday*100,2):0
            ])
            ->setClass('p-6');
        $builder->add($statistic, [
            'xs' => 24,
            'sm' => 12,
            'md' => 6,
            'lg' => 4,
            'class' => 'bg-white p-4 rounded-4 shadow-lighter'
        ]);
        $statistic = new Statistic;
        $statistic->setLabel('快捷操作')
        ->setFooterText('操作提示')
            ->setData([
                'today' => 100,
                'yesterday' => 124,
                'growth_rate' => 100,
                'footer' => 100
            ])
            ->setAction([
                'model' => Action::REDIRECT['value'],
                'path' => '/app/pluginExample/admin/Table/index',
                'component' => [
                    'name' => 'button',
                    'label' => '去操作',
                    'props' => [
                        'type' => 'success',
                        'size' => 'small'
                    ]
                ]
            ])
            ->setClass('p-6');
        $builder->add($statistic, [
            'xs' => 24,
            'sm' => 12,
            'md' => 12,
            'lg' => 6,
            'xl' => 4,
            'class' => 'bg-white p-4 rounded-4 shadow-lighter'
        ]);
        $Domain = new Domain;
        $Domain->setLabel('复制链接')
            ->setTips('GitHub')
            ->setDomain('https://github.com/RenLoong/loong-admin.git');
        $Domain->setAgreement([
            'title' => 'LoongAdmin',
            'url' => 'https://github.com/RenLoong/loong-admin.git'
        ]);
        $builder->add($Domain, [
            'xs' => 24,
            'sm' => 24,
            'md' => 24,
            'lg' => 24,
            'xl' => 8,
            'class' => 'bg-white p-4 rounded-4 shadow-lighter'
        ], [
            'xs' => 1,
            'sm' => 1,
            'md' => 1,
            'lg' => 1,
            'xl' => 2,
        ]);
        $this->echarts($builder);
        return $this->resData($builder);
    }
    public function echarts(DataboardBuilder $builder)
    {
        $request=request();
        $echarts = new Echarts;
        $echarts->setClass('bg-white p-4 rounded-4 shadow-lighter vh-65');
        $EchartsData=$this->getEchartsData($request);
        $echarts->setData($EchartsData);
        $builder->add($echarts);
    }
    public function getEchartsData(Request $request)
    {
        $data=[
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
                'data' => ["注册人数"]
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
                    'name' => "注册人数",
                    'type' => 'line',
                    'smooth' => true,
                    'data' => []
                ]
            ]
        ];
        for($i=7;$i>0;$i--){
            $date=date('Y-m-d',strtotime("-$i day"));
            $data['xAxis'][0]['data'][]=$date;
            $Statistic=rand(100,1000);
            if($Statistic){
                $data['series'][0]['data'][]=$Statistic;
            }else{
                $data['series'][0]['data'][]=0;
            }
        }
        return $data;
    }
}