<?php

return [
    '' => [
        app\middleware\Template::class,
        app\middleware\Access::class,
        app\middleware\Platform::class
    ],
    'control' => [
        plugin\control\expose\middleware\ControlAuth::class
    ],
    'admin' => [
        app\expose\middleware\AdminAuth::class
    ],
    'api' => [
        plugin\control\expose\middleware\DomainMapping::class,
        plugin\user\expose\middleware\UserAuth::class,
        plugin\user\expose\middleware\Twofa::class,
    ]
];
