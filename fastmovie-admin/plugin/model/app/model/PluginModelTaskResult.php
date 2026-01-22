<?php

namespace plugin\model\app\model;

use app\model\Basic;
use plugin\control\expose\helper\Uploads;

class PluginModelTaskResult extends Basic
{
    protected function getOptions(): array
    {
        return [
            'type' => [
                // 设置JSON字段的类型
                'params'    =>    'json',
                'result'    =>    'json',
            ]
        ];
    }
    public function getImagePathAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setImagePathAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function getAudioPathAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setAudioPathAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function getVideoPathAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setVideoPathAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
}
