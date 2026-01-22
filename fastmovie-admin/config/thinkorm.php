<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            // 数据库类型
            'type' => 'mysql',
            // 服务器地址
            'hostname' => getenv('DATABASE_HOST'),
            // 数据库名
            'database' => getenv('DATABASE_NAME'),
            // 数据库用户名
            'username' => getenv('DATABASE_USERNAME'),
            // 数据库密码
            'password' => getenv('DATABASE_PASSWORD'),
            // 数据库连接端口
            'hostport' => getenv('DATABASE_PORT'),
            // 数据库连接参数
            'params' => [
                // 连接超时3秒
                \PDO::ATTR_TIMEOUT => 3,
            ],
            // 数据库编码默认采用utf8
            'charset' => getenv('DATABASE_CHARSET'),
            // 数据库表前缀
            'prefix' => getenv('DATABASE_PREFIX'),
            // 断线重连
            'break_reconnect' => true,
            // 关闭SQL监听日志
            'trigger_sql' => false,
            // 自定义分页类
            'bootstrap' =>  '',
            'pool' => [ // 连接池配置，仅支持swoole/swow驱动
                'max_connections' => (int)getenv('DATABASE_MAX_CONNECTIONS'), // 最大连接数
                'min_connections' => (int)getenv('DATABASE_MIN_CONNECTIONS'), // 最小连接数
                'wait_timeout' => (int)getenv('DATABASE_WAIT_TIMEOUT'),    // 从连接池获取连接等待的最大时间，超时后会抛出异常
                'idle_timeout' => (int)getenv('DATABASE_IDLE_TIMEOUT'),   // 连接池中连接最大空闲时间，超时后会关闭回收，直到连接数为min_connections
                'heartbeat_interval' => (int)getenv('DATABASE_HEARTBEAT_INTERVAL'), // 连接池心跳检测时间，单位秒，建议小于60秒
            ],
        ],
    ],
];
