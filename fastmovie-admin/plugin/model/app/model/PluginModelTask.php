<?php

namespace plugin\model\app\model;

use app\model\Basic;
use plugin\user\app\model\PluginUser;
use plugin\control\app\model\PluginChannelsUser;
use plugin\model\utils\enum\ModelTaskStatus;

class PluginModelTask extends Basic
{
    protected function getOptions(): array
    {
        return [
            'type' => [
                'consume_ids' => 'json',
            ]
        ];
    }
    public function result()
    {
        return $this->hasOne(PluginModelTaskResult::class, 'task_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(PluginUser::class, 'id', 'uid');
    }

    public function channels()
    {
        return $this->hasOne(PluginChannelsUser::class, 'id', 'channels_uid');
    }
    public static function processing($where = [])
    {
        return self::where($where)->whereIn('status', [ModelTaskStatus::WAIT['value'],ModelTaskStatus::PROCESSING['value'], ModelTaskStatus::WAIT_DOWNLOAD['value'], ModelTaskStatus::DOWNLOADING['value'], ModelTaskStatus::UPLOADING['value']])->count();
    }
}
