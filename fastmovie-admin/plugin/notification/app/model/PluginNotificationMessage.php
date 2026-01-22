<?php

namespace plugin\notification\app\model;


use app\model\Basic;
use plugin\control\app\model\PluginChannelsUser;
use plugin\user\app\model\PluginUser;

class PluginNotificationMessage extends Basic
{
    protected function getOptions(): array
    {
        return [
            'type' => [
                'extra'  =>  'json'
            ],
        ];
    }
    public function user()
    {
        return $this->hasOne(PluginUser::class, 'id', 'uid');
    }
    public function channels()
    {
        return $this->hasOne(PluginChannelsUser::class, 'id', 'channels_uid');
    }
    public function content()
    {
        return $this->hasOne(PluginNotificationMessageContent::class, 'message_id', 'id');
    }
}
