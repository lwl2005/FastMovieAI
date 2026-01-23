<?php

use Webman\Route;

Route::get('/fastmovie', [app\controller\IndexController::class, 'fastmovie']);
Route::get('/fastmovie/', [app\controller\IndexController::class, 'fastmovie']);
Route::any('/notify/wechat/{plugin}/{template_id}', [app\controller\NotifyController::class, 'wechat']);
require_once app_path('admin/config/route.php');
