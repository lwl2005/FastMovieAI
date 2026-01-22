<?php

namespace plugin\shortplay\utils\enum;

use app\expose\enum\builder\Enum;

class ActorSpeciesType extends Enum
{
    const HUMAN = [
        'label' => '人类',
        'value' => 'human',
    ];
    const ANIMAL = [
        'label' => '动物',
        'value' => 'animal',
    ];
    const PLANT = [
        'label' => '植物',
        'value' => 'plant',
    ];
    const ROBOT = [
        'label' => '机器人',
        'value' => 'robot',
    ];
    const VIRTUAL_PERSON = [
        'label' => '虚拟人',
        'value' => 'virtual_person',
    ];
    const DOLL = [
        'label' => '玩偶',
        'value' => 'doll',
    ];
    const SPRITE = [
        'label' => '精灵',
        'value' => 'sprite',
    ];
    const GHOST = [
        'label' => '鬼怪',
        'value' => 'ghost',
    ];
    const OTHER = [
        'label' => '其他',
        'value' => 'other',
    ];
}
