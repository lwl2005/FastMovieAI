<?php

return [
    // 'admin' => [
    //     plugin\article\app\admin\middleware\Auth::class
    // ]
    '' => [
        app\expose\middleware\Platform::class
    ],
    'admin' => [
        app\expose\middleware\AdminAuth::class
    ],
    'control' => [
        plugin\control\expose\middleware\ControlAuth::class
    ],
    'api' => [
        plugin\control\expose\middleware\DomainMapping::class,
        plugin\user\expose\middleware\UserAuth::class,
        plugin\user\expose\middleware\Twofa::class,
    ]
];
