<?php

namespace support;

use plugin\notification\expose\helper\Online;
use Workerman\Worker;

class Event
{
    // 在 Worker 启动时执行
    public static function start(Worker $worker)
    {
        if ($worker->name == 'plugin.webman.push.server') {
            echo "Worker {$worker->name} 启动成功\n";
            Online::clear();
            echo "Worker {$worker->name} 清除历史在线用户成功\n";
        }
    }
}
