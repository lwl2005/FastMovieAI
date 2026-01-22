<?php

namespace plugin\marketing\app\admin\controller;

use app\Basic;
use app\expose\build\builder\DataboardBuilder;
use app\expose\build\builder\databoardBuilder\component\Statistic;
use plugin\marketing\app\model\PluginMarketingCoupon;
use plugin\marketing\app\model\PluginMarketingCouponCode;
use support\Request;

class IndexController extends Basic
{
    public function index(Request $request)
    {
        $builder = new DataboardBuilder([
            'gutter' => 6
        ]);
        $today=PluginMarketingCoupon::whereDay('create_time')->count();
        $yesterday=PluginMarketingCoupon::whereDay('create_time','yesterday')->count();
        $statistic = new Statistic;
        $statistic->setLabel('新增优惠券')
            ->setUnit('')
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

        $today=PluginMarketingCouponCode::whereDay('create_time')->count();
        $yesterday=PluginMarketingCouponCode::whereDay('create_time','yesterday')->count();
        $statistic = new Statistic;
        $statistic->setLabel('发行券码')
            ->setUnit('')
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

        $today=PluginMarketingCouponCode::whereDay('receive_time')->count();
        $yesterday=PluginMarketingCouponCode::whereDay('receive_time','yesterday')->count();
        $statistic = new Statistic;
        $statistic->setLabel('领取优惠券')
            ->setUnit('')
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

        $today=PluginMarketingCouponCode::whereDay('use_time')->count();
        $yesterday=PluginMarketingCouponCode::whereDay('use_time','yesterday')->count();
        $statistic = new Statistic;
        $statistic->setLabel('使用优惠券')
            ->setUnit('')
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
        return $this->resData($builder);
    }
}
