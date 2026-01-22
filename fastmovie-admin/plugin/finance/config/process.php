<?php
use plugin\finance\process\Expire;
use Workerman\Events\Swoole;

return [
    'OrdersExpire'  => [
        'eventLoop' => Swoole::class,
        'handler'  => Expire::class
    ],
];
