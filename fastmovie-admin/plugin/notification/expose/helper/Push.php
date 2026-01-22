<?php

namespace plugin\notification\expose\helper;

use plugin\notification\app\model\PluginNotificationOnline;
use plugin\notification\utils\Api;

class Push
{
    public static function send(array $where = [], mixed $data = [], string $event = 'message'): bool
    {
        $PluginNotificationOnline = PluginNotificationOnline::where($where)->column('channel');
        if (!empty($PluginNotificationOnline)) {
            return self::trigger($PluginNotificationOnline, $event, $data);
        }
        return false;
    }
    public static function trigger(string|array $channel, string $event, array $data): bool
    {
        $api = new Api(
            config('plugin.webman.push.app.api'),
            config('plugin.webman.push.app.app_key'),
            config('plugin.webman.push.app.app_secret')
        );
        return $api->trigger($channel, $event, $data);
    }
}
