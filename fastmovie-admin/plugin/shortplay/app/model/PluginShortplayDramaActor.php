<?php

namespace plugin\shortplay\app\model;

use app\model\Basic;
use plugin\control\expose\helper\Uploads;

class PluginShortplayDramaActor extends Basic
{
    protected function getOptions(): array
    {
        return [
            'type' => [
                // 设置JSON字段的类型
                'voice'    =>    'json',
            ]
        ];
    }
    public function actor()
    {
        return $this->hasOne(PluginShortplayActor::class, 'id', 'actor_id');
    }
    public function getThreeViewImageAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setThreeViewImageAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function getHeadimgAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setHeadimgAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
}
