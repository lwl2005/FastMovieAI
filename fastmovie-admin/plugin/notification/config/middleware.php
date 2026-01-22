<?php

return [
    '' => [
        app\expose\middleware\Platform::class
    ],
    'admin' => [
        app\expose\middleware\AdminAuth::class
    ],
    'api' => [
        plugin\control\expose\middleware\DomainMapping::class,
        plugin\user\expose\middleware\UserAuth::class,
        plugin\user\expose\middleware\Twofa::class,
    ],
];
