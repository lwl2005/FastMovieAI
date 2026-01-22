<?php

namespace plugin\shortplay\utils\enum;

use app\expose\enum\builder\Enum;

class ActorAge extends Enum
{
    const CHILD = [
        'label' => '儿童',
        'value' => 'child',
    ];
    const TEENAGER = [
        'label' => '少年',
        'value' => 'teenager',
    ];
    const YOUNG = [
        'label' => '青年',
        'value' => 'young',
    ];
    const MIDDLE_AGE = [
        'label' => '中年',
        'value' => 'middle_age',
    ];
    const OLD = [
        'label' => '老年',
        'value' => 'old',
    ];
}