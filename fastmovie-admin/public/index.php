<?php
/**
 * FastMovie Admin 入口文件
 * 检测系统是否已安装，未安装则跳转到安装页面
 */

// 检查是否已安装
$lockFile = dirname(__DIR__) . '/install.lock';

// 获取当前请求路径
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// 如果不在安装页面且未安装，则跳转到安装页面
if (strpos($path, '/install') !== 0 && !file_exists($lockFile)) {
    // 重定向到安装页面
    header('Location: /install');
    exit;
}

// 如果已安装或在安装页面，继续正常流程
// 这里可以添加其他初始化逻辑
