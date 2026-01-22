<?php

use plugin\article\process\JoinPushMessage;
use Workerman\Events\Swoole;

return [
    'JoinPushMessage' => [
        'eventLoop' => Swoole::class,
        'handler'  => JoinPushMessage::class
    ],
];
