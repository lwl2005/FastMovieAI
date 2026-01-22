<?php

namespace plugin\article\utils\enum;

use app\expose\enum\builder\Enum;

class CrowdEnum extends Enum
{
    const ALL = [
        'label' => '全部',
        'value' => 'all',
    ];
    const SPECIFIED = [
        'label' => '指定',
        'value' => 'specified',
    ];
    // const WEEK = [
    //     'label' => '一周内',
    //     'value' => 'week',
    // ];
    // const MONTH = [
    //     'label' => '一个月',
    //     'value' => 'month',
    // ];
}