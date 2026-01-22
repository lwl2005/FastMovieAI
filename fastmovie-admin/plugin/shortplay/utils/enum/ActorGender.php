<?php

namespace plugin\shortplay\utils\enum;

use app\expose\enum\builder\Enum;

class ActorGender extends Enum
{
    const MALE = [
        'label' => '男性',
        'value' => 'male',
    ];
    const FEMALE = [
        'label' => '女性',
        'value' => 'female',
    ];
    const NONE = [
        'label' => '无性别',
        'value' => 'none',
    ];
}