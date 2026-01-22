<?php

namespace plugin\notification\expose\helper;

use think\facade\Db;

class Online
{
    public static function clear()
    {
        // 截断表
        Db::execute('TRUNCATE TABLE ' . getenv('DATABASE_PREFIX') . 'plugin_notification_online');
    }
}
