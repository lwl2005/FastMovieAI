<?php
use plugin\marketing\process\Expire;
use Workerman\Events\Swoole;

return [
    'CouponExpire'  => [
        'eventLoop' => Swoole::class,
        'handler'  => Expire::class
    ],
];
