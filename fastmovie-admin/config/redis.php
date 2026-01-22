<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

return [
    'default' => [
        'host' => getenv('REDIS_HOST'),
        'password' => getenv('REDIS_PASSWORD'),
        'port' => getenv('REDIS_PORT'),
        'database' => getenv('REDIS_DATABASE'),
        'pool' => [ // 连接池配置，仅支持swoole/swow驱动
            'max_connections' => (int)getenv('REDIS_MAX_CONNECTIONS'), // 最大连接数
            'min_connections' => (int)getenv('REDIS_MIN_CONNECTIONS'), // 最小连接数
            'wait_timeout' => (int)getenv('REDIS_WAIT_TIMEOUT'),    // 从连接池获取连接等待的最大时间，超时后会抛出异常
            'idle_timeout' => (int)getenv('REDIS_IDLE_TIMEOUT'),   // 连接池中连接最大空闲时间，超时后会关闭回收，直到连接数为min_connections
            'heartbeat_interval' => (int)getenv('REDIS_HEARTBEAT_INTERVAL'), // 连接池心跳检测时间，单位秒，建议小于60秒
        ],
    ],
];
