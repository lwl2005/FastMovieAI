<?php

use plugin\model\process\audio\AudioTransfer;
use plugin\model\process\chat\Submit;
use plugin\model\process\draw\ImageTransfer;
use plugin\model\process\video\VideoTransfer;
use Workerman\Events\Swoole;

return [
    'ChatSubmit' => [
        'eventLoop' => Swoole::class,
        'handler'  => Submit::class,
        'count' => 5
    ],
    'ImageTransfer' => [
        'eventLoop' => Swoole::class,
        'handler'  => ImageTransfer::class,
        'count' => 5
    ],
    'VideoTransfer' => [
        'eventLoop' => Swoole::class,
        'handler'  => VideoTransfer::class,
        'count' => 5
    ],
    'AudioTransfer' => [
        'eventLoop' => Swoole::class,
        'handler'  => AudioTransfer::class,
        'count' => 5
    ],
];
