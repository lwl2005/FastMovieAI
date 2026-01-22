<?php

namespace plugin\shortplay\utils\enum;

use app\expose\enum\builder\Enum;

class VoiceEmotion extends Enum
{
    const NEUTRAL = [
        'label' => '中性',
        'value' => 'neutral'
    ];
    const FEARFUL = [
        'label' => '恐惧',
        'value' => 'fearful'
    ];
    const ANGRY = [
        'label' => '愤怒',
        'value' => 'angry'
    ];
    const SAD = [
        'label' => '悲伤',
        'value' => 'sad'
    ];
    const SURPRISED = [
        'label' => '惊讶',
        'value' => 'surprised'
    ];
    const HAPPY = [
        'label' => '高兴',
        'value' => 'happy'
    ];
    const DISGUSTED = [
        'label' => '厌恶',
        'value' => 'disgusted'
    ];
}
