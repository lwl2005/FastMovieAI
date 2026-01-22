<?php

namespace plugin\shortplay\utils\enum;

use app\expose\enum\builder\Enum;

class StyleClassify extends Enum
{
    const ANIME = [
        'label' => '动漫风格',
        'value' => 'anime',
    ];
    const REALISTIC = [
        'label' => '写实风格',
        'value' => 'realistic',
    ];
    const PORTRAIT = [
        'label' => '肖像风格',
        'value' => 'portrait',
    ];
    const COMIC = [
        'label' => '漫画风格',
        'value' => 'comic',
    ];
    const OTHER = [
        'label' => '其他风格',
        'value' => 'other',
    ];
}
