<?php

namespace plugin\user\api\admin;

use app\expose\build\builder\DataboardBuilder;
use app\expose\build\builder\databoardBuilder\component\Statistic;
use plugin\user\app\model\PluginUser;

class IndexController
{
    public function control(DataboardBuilder $builder)
    {
        $today = PluginUser::whereDay('create_time')->count();
        $yesterday = PluginUser::whereDay('create_time', 'yesterday')->count();
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
    }
}
