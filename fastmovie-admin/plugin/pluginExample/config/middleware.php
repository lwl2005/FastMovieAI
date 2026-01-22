<?php

return [
    '' => [
        app\middleware\Template::class,
        app\middleware\Access::class,
        app\middleware\Platform::class
    ],
    'admin' => [
        app\expose\middleware\AdminAuth::class
    ]
];
