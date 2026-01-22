<?php

namespace plugin\shortplay\app\model;

use app\model\Basic;
use plugin\control\expose\helper\Uploads;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;

class PluginShortplayDramaStoryboardDialogue extends Basic
{
    protected function getOptions(): array
    {
        return [
            'type' => [
                // 设置JSON字段的类型
                'prosody_speed'    =>    'float',
                'prosody_volume'    =>    'float',
                'voice'    =>    'json',
            ]
        ];
    }
    public function getAudioAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setAudioAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function actor()
    {
        return $this->hasOne(PluginShortplayActor::class, 'id', 'actor_id');
    }
    public function storyboard()
    {
        return $this->hasOne(PluginShortplayDramaStoryboard::class, 'id', 'storyboard_id');
    }
    public static function onAfterRead($model)
    {
        $model->voice_state = PluginModelTask::processing(['alias_id' => $model->id, 'scene' => ModelScene::DIALOGUE_VOICE['value']]);
    }
}
