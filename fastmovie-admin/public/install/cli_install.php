<?php

/**
 * 命令行安装脚本
 * 使用方法：php cli_install.php
 */

// 只允许命令行执行
if (php_sapi_name() !== 'cli') {
    die('此脚本只能在命令行中执行');
}

define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
define('SQL_FILE', ROOT_PATH . 'database.sql');

echo "=================================\n";
echo "FastMovie Admin 命令行安装工具\n";
echo "=================================\n\n";

// 读取配置
session_start();
$config = $_SESSION['install_config'] ?? [];

if (empty($config)) {
    echo "错误：未找到安装配置\n";
    echo "请先通过Web界面完成前3步配置\n";
    exit(1);
}

echo "数据库配置：\n";
echo "  主机：{$config['db_host']}:{$config['db_port']}\n";
echo "  数据库：{$config['db_name']}\n";
echo "  用户：{$config['db_user']}\n";
echo "  前缀：{$config['db_prefix']}\n\n";

try {
    echo "[1/5] 连接数据库...\n";
    $pdo = new PDO(
        "mysql:host={$config['db_host']};port={$config['db_port']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ 数据库连接成功\n\n";

    echo "[2/5] 创建数据库...\n";
    $dbName = $config['db_name'];
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARSET utf8mb4");
    $pdo->exec("USE `$dbName`");
    echo "✓ 数据库创建成功\n\n";

    echo "[3/5] 导入SQL文件...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("SET AUTOCOMMIT=0");

    $fp = fopen(SQL_FILE, 'r');
    $prefix = $config['db_prefix'];
    $count = 0;
    $errors = 0;

    $pdo->beginTransaction();

    while ($sql = getNextSQL($fp, $prefix)) {
        try {
            $pdo->exec($sql);
            $count++;

            if ($count % 50 == 0) {
                $pdo->commit();
                $pdo->beginTransaction();
                echo "  已执行 $count 条SQL...\r";
            }
        } catch (PDOException $e) {
            if (stripos($e->getMessage(), 'already exists') === false) {
                $errors++;
            }
        }
    }

    $pdo->commit();
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    $pdo->exec("SET AUTOCOMMIT=1");
    fclose($fp);

    echo "\n✓ SQL导入完成，共 $count 条\n\n";

    echo "[4/5] 创建管理员账号...\n";
    $pdo->exec("DELETE FROM `{$prefix}admin` WHERE id=1");

    $stmt = $pdo->prepare("INSERT INTO `{$prefix}admin` 
        (id, username, password, nickname, role_id, state, create_time, update_time) 
        VALUES (1, ?, ?, ?, 1, 1, NOW(), NOW())");

    $stmt->execute([
        $config['admin_user'],
        password_hash($config['admin_pass'], PASSWORD_BCRYPT),
        $config['admin_nickname']
    ]);
    echo "✓ 管理员创建成功\n\n";

    echo "[5/5] 生成配置文件...\n";
    $env = generateEnv($config);
    file_put_contents(ROOT_PATH . '.env', $env);
    file_put_contents(ROOT_PATH . 'install.lock', date('Y-m-d H:i:s'));
    echo "✓ 配置文件生成成功\n\n";

    echo "=================================\n";
    echo "🎉 安装完成！\n";
    echo "=================================\n";
    echo "管理员账号：{$config['admin_user']}\n";
    echo "后台地址：http://你的域名/admin\n";
    echo "\n请删除 public/install 目录\n";
} catch (Exception $e) {
    echo "\n❌ 错误：" . $e->getMessage() . "\n";
    exit(1);
}

function getNextSQL($fp, $prefix)
{
    $sql = '';
    while ($line = fgets($fp, 40960)) {
        $line = trim($line);
        if (empty($line) || substr($line, 0, 2) == '--' || $line[0] == '#') continue;

        if ($prefix != 'php_') {
            $line = str_replace('`php_', "`$prefix", $line);
            $line = str_replace('INTO php_', "INTO $prefix", $line);
        }

        $sql .= $line . ' ';
        if (substr($line, -1) == ';') return trim($sql);
    }
    return '';
}

function generateEnv($c)
{
    $key = bin2hex(random_bytes(16));
    $secret = bin2hex(random_bytes(16));

    return "DEBUG = false

SERVER_NAME = FastMovieAdmin
SERVER_PORT = 36999
SERVER_ADMIN_PATH = admin

DATABASE_HOST = {$c['db_host']}
DATABASE_PORT = {$c['db_port']}
DATABASE_NAME = {$c['db_name']}
DATABASE_USERNAME = {$c['db_user']}
DATABASE_PASSWORD = {$c['db_pass']}
DATABASE_CHARSET = utf8mb4
DATABASE_PREFIX = {$c['db_prefix']}

REDIS_HOST = {$c['redis_host']}
REDIS_PORT = {$c['redis_port']}
REDIS_PASSWORD = {$c['redis_pass']}
REDIS_DATABASE = {$c['redis_db']}

PUSH_KEY = $key
PUSH_SCERET = $secret
PUSH_API_PORT = 37000
PUSH_WSS_PORT = 37001
";
}
