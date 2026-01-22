<?php

/**
 * FastMovie Admin 安装向导
 * 简化版 - 推荐使用命令行安装
 */

// 防止重复安装
if (file_exists('../../install.lock')) {
    showAlreadyInstalled();
    exit;
}

define('INSTALL_VERSION', '1.0.0');
define('MIN_PHP_VERSION', '7.4.0');
define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
define('SQL_FILE', ROOT_PATH . 'database.sql');

date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=UTF-8');

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

switch ($step) {
    case 1:
        showStep1();
        break;
    case 2:
        showStep2();
        break;
    case 3:
        if (isset($_GET['check_db'])) {
            checkDatabase();
        } elseif (isset($_GET['check_redis'])) {
            checkRedis();
        } else {
            showStep3();
        }
        break;
    case 4:
        if (isset($_GET['install'])) {
            doInstall();
        } else {
            showStep4();
        }
        break;
    default:
        showStep1();
}

// 步骤1: 许可协议
function showStep1()
{
    include 'templates/step1.php';
}

// 步骤2: 环境检测
function showStep2()
{
    $checks = checkEnvironment();
    $sqlFileExists = file_exists(SQL_FILE);
    $canContinue = $checks['php_version'] && $checks['mysqli'] && $checks['curl'] &&
        $checks['gd'] && $checks['redis_ext'] && $checks['pdo'] &&
        $checks['webman_functions'] && $sqlFileExists;
    include 'templates/step2.php';
}

// 步骤3: 参数配置
function showStep3()
{
    include 'templates/step3.php';
}

// 步骤4: 开始安装
function showStep4()
{
    session_start();
    if (!empty($_POST)) {
        $_SESSION['install_config'] = $_POST;
    }
    include 'templates/step4.php';
}

// 执行安装
function doInstall()
{
    // 关闭所有错误输出，防止干扰 SSE 流
    error_reporting(0);
    ini_set('display_errors', '0');
    
    session_start();
    $config = $_SESSION['install_config'] ?? [];

    if (empty($config)) {
        sendLog('error', '错误：未找到安装配置');
        exit;
    }

    // 初始化日志文件
    $logFile = __DIR__ . '/install.log';
    @file_put_contents($logFile, ''); // 清空旧日志
    
    // 定义全局日志文件路径
    define('INSTALL_LOG_FILE', $logFile);

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    
    // 清空输出缓冲区
    if (ob_get_level()) {
        ob_end_clean();
    }

    sendLog('info', '🚀 开始安装 FastMovie Admin...');
    sendLog('info', '安装时间：' . date('Y-m-d H:i:s'));
    sendLog('info', '');
    
    // 检查目录权限
    sendLog('info', '检查目录权限...');
    $rootPath = ROOT_PATH;
    $rootPerms = substr(sprintf('%o', fileperms($rootPath)), -4);
    sendLog('info', "根目录：{$rootPath}");
    sendLog('info', "当前权限：{$rootPerms}");
    
    if (!is_writable($rootPath)) {
        sendLog('error', '');
        sendLog('error', '❌ 根目录不可写！');
        sendLog('error', '');
        sendLog('error', '解决方法：');
        sendLog('error', '1. 使用 SSH 执行：chmod -R 755 ' . $rootPath);
        sendLog('error', '2. 或在宝塔面板：文件 → 权限 → 设置为 755 或 777');
        sendLog('error', '3. 确保 Web 服务器用户（如 www）有写入权限');
        sendLog('error', '');
        exit;
    }
    sendLog('success', '✓ 目录权限检查通过');
    sendLog('info', '');

    try {
        // 1. 连接数据库
        sendLog('info', '[1/5] 连接数据库...');
        $pdo = new PDO(
            "mysql:host={$config['db_host']};port={$config['db_port']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        sendLog('success', '✓ 数据库连接成功');
        sendLog('info', '');

        // 2. 创建数据库
        sendLog('info', '[2/5] 创建数据库...');
        $dbName = $config['db_name'];
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARSET utf8mb4");
        $pdo->exec("USE `$dbName`");
        sendLog('success', "✓ 数据库 {$dbName} 准备就绪");
        sendLog('info', '');

        // 3. 导入SQL
        sendLog('info', '[3/5] 导入SQL文件...');
        sendLog('info', '正在读取 database.sql...');

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
                    sendLog('sql', $sql);
                    flush();
                    ob_flush();
                } elseif ($count % 10 == 0) {
                    sendLog('sql', $sql);
                    flush();
                    ob_flush();
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

        sendLog('success', "✓ SQL导入完成，共执行 {$count} 条");
        if ($errors > 0) {
            sendLog('info', "忽略 {$errors} 个非致命错误");
        }
        sendLog('info', '');

        // 4. 创建管理员
        sendLog('info', '[4/5] 创建管理员账号...');
        $pdo->exec("DELETE FROM `{$prefix}admin` WHERE id=1");

        $stmt = $pdo->prepare("INSERT INTO `{$prefix}admin` 
            (id, username, password, nickname, role_id, state, create_time, update_time) 
            VALUES (1, ?, ?, ?, 1, 1, NOW(), NOW())");

        $stmt->execute([
            $config['admin_user'],
            password_hash($config['admin_pass'], PASSWORD_BCRYPT),
            $config['admin_nickname']
        ]);
        sendLog('success', "✓ 管理员 {$config['admin_user']} 创建成功");
        sendLog('info', '');

        // 5. 生成配置文件
        sendLog('info', '[5/5] 生成配置文件...');
        $envData = generateEnv($config);
        
        // 检查根目录是否可写
        $rootPath = ROOT_PATH;
        if (!is_writable($rootPath)) {
            throw new Exception("根目录不可写：{$rootPath}，请设置目录权限为 755 或 777");
        }
        
        // 写入 .env 文件
        $envFile = ROOT_PATH . '.env';
        if (file_put_contents($envFile, $envData['content']) === false) {
            $error = error_get_last();
            $errorMsg = $error ? $error['message'] : '未知错误';
            throw new Exception("无法写入 .env 文件：{$envFile}\n错误：{$errorMsg}\n请检查目录权限（建议 755 或 777）");
        }
        sendLog('success', '✓ .env 文件创建成功');
        
        // 创建 lock 文件
        $lockFile = ROOT_PATH . 'install.lock';
        $lockContent = date('Y-m-d H:i:s') . "\n" . 
                       "管理员：{$config['admin_user']}\n" .
                       "数据库：{$config['db_name']}\n";
        
        if (file_put_contents($lockFile, $lockContent) === false) {
            $error = error_get_last();
            $errorMsg = $error ? $error['message'] : '未知错误';
            throw new Exception("无法创建 install.lock 文件：{$lockFile}\n错误：{$errorMsg}\n请检查目录权限（建议 755 或 777）");
        }
        sendLog('success', '✓ 安装锁定文件已创建');
        
        $pushKey = $envData['pushKey'];
        sendLog('success', '✓ 配置文件生成成功');
        sendLog('info', '');

        sendLog('success', '=================================');
        sendLog('success', '🎉 安装完成！');
        sendLog('success', '=================================');
        sendLog('info', "管理员账号：{$config['admin_user']}");
        sendLog('info', '后台地址：http://你的域名/admin');
        sendLog('info', '');
        sendLog('info', '📋 下一步操作：');
        sendLog('info', '');
        sendLog('info', '1️⃣ 配置伪静态规则');
        sendLog('info', '   复制 nginx.example 文件内容到站点伪静态配置');
        sendLog('info', '   PUSH_KEY 已自动更新为：' . substr($pushKey, 0, 16) . '...');
        sendLog('info', '');
        sendLog('info', '2️⃣ 启动后端服务');
        sendLog('info', '   命令：php start.php start -d');
        sendLog('info', '   或在宝塔面板配置进程守护');
        sendLog('info', '');
        sendLog('info', '3️⃣ 删除安装目录');
        sendLog('info', '   ⚠️ 删除 public/install 目录（重要！）');
        sendLog('info', '');
        sendLog('info', '📝 安装日志已保存到：public/install/install.log');
        sendLog('info', '');
        sendLog('done', 'INSTALL_COMPLETE');
    } catch (Exception $e) {
        sendLog('error', '');
        sendLog('error', '❌ 安装失败：' . $e->getMessage());
        sendLog('error', '错误文件：' . $e->getFile());
        sendLog('error', '错误行号：' . $e->getLine());
        sendLog('error', '');
        sendLog('error', '请检查：');
        sendLog('error', '1. 数据库连接信息是否正确');
        sendLog('error', '2. 数据库用户是否有足够权限');
        sendLog('error', '3. SQL文件是否完整');
        sendLog('error', '4. 目录是否有写入权限');
        sendLog('error', '');
        sendLog('error', '📝 详细错误信息已保存到：public/install/install.log');
    }

    exit;
}

function sendLog($type, $message)
{
    // 跳过空消息
    if ($message === '' && $type !== 'info') {
        return;
    }
    
    $data = ['type' => $type, 'message' => $message];
    if ($type === 'sql') {
        // 截断过长的SQL
        if (strlen($message) > 200) {
            $message = substr($message, 0, 200) . '...';
        }
        $data['message'] = $message;
    }
    
    // 输出到浏览器 - 确保格式正确
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    if ($jsonData !== false) {
        echo "data: " . $jsonData . "\n\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
    
    // 写入日志文件
    if (defined('INSTALL_LOG_FILE')) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$type] $message\n";
        @file_put_contents(INSTALL_LOG_FILE, $logMessage, FILE_APPEND);
    }
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
    // 生成随机的 32 位字符串
    $pushKey = bin2hex(random_bytes(16));
    $pushSecret = bin2hex(random_bytes(16));

    // 读取 .env.example 作为模板
    $envExample = file_get_contents(ROOT_PATH . '.env.example');
    
    if ($envExample === false) {
        // 如果读取失败，使用默认模板
        sendLog('info', '⚠️ 未找到 .env.example，使用默认配置');
        $envExample = "DEBUG = false

SERVER_NAME = LoongAdmin
SERVER_PORT = 36999
SERVER_ADMIN_PATH = admin

DATABASE_HOST = 127.0.0.1
DATABASE_PORT = 3306
DATABASE_NAME = 
DATABASE_USERNAME = 
DATABASE_PASSWORD = 
DATABASE_CHARSET = utf8mb4
DATABASE_PREFIX = php_
DATABASE_MAX_CONNECTIONS = 10
DATABASE_MIN_CONNECTIONS = 1
DATABASE_WAIT_TIMEOUT = 3
DATABASE_IDLE_TIMEOUT = 60
DATABASE_HEARTBEAT_INTERVAL = 50

REDIS_HOST = 127.0.0.1
REDIS_PORT = 6379
REDIS_PASSWORD =
REDIS_DATABASE = 2

PUSH_KEY = 32位字符串
PUSH_SCERET = 32位字符串
PUSH_API_PORT = 37000
PUSH_WSS_PORT = 37001
";
    }

    // 替换配置值
    $replacements = [
        'DATABASE_HOST = ' => "DATABASE_HOST = {$c['db_host']}",
        'DATABASE_PORT = ' => "DATABASE_PORT = {$c['db_port']}",
        'DATABASE_NAME = ' => "DATABASE_NAME = {$c['db_name']}",
        'DATABASE_USERNAME = ' => "DATABASE_USERNAME = {$c['db_user']}",
        'DATABASE_PASSWORD = ' => "DATABASE_PASSWORD = {$c['db_pass']}",
        'DATABASE_PREFIX = ' => "DATABASE_PREFIX = {$c['db_prefix']}",
        'REDIS_HOST = ' => "REDIS_HOST = {$c['redis_host']}",
        'REDIS_PORT = ' => "REDIS_PORT = {$c['redis_port']}",
        'REDIS_PASSWORD =' => "REDIS_PASSWORD = {$c['redis_pass']}",
        'REDIS_DATABASE = ' => "REDIS_DATABASE = {$c['redis_db']}",
        'PUSH_KEY = ' => "PUSH_KEY = {$pushKey}",
        'PUSH_SCERET = ' => "PUSH_SCERET = {$pushSecret}",
    ];

    $env = $envExample;
    foreach ($replacements as $search => $replace) {
        // 使用正则替换整行
        $pattern = '/^' . preg_quote($search, '/') . '.*$/m';
        $env = preg_replace($pattern, $replace, $env);
    }

    // 同步更新 nginx.example 中的 PUSH_KEY
    updateNginxExample($pushKey);

    return [
        'content' => $env,
        'pushKey' => $pushKey,
        'pushSecret' => $pushSecret
    ];
}

/**
 * 更新 nginx.example 中的 PUSH_KEY
 */
function updateNginxExample($pushKey)
{
    $nginxFile = ROOT_PATH . 'nginx.example';
    
    if (!file_exists($nginxFile)) {
        sendLog('info', '⚠️ 未找到 nginx.example 文件');
        return;
    }

    $content = file_get_contents($nginxFile);
    
    // 替换 /app/PUSH_KEY 为实际的 PUSH_KEY
    $newContent = preg_replace(
        '/location\s+\/app\/PUSH_KEY\s*\{/',
        "location /app/{$pushKey} {",
        $content
    );

    if ($newContent !== $content) {
        file_put_contents($nginxFile, $newContent);
        sendLog('success', "✓ nginx.example 已更新 PUSH_KEY");
    }
}

// 环境检测
function checkEnvironment()
{
    $checks = [];

    $phpVersion = phpversion();
    $checks['php_version'] = version_compare($phpVersion, MIN_PHP_VERSION, '>=');

    // 基础扩展检测
    $checks['pdo'] = extension_loaded('pdo') && extension_loaded('pdo_mysql');
    $checks['mysqli'] = extension_loaded('mysqli');
    $checks['redis_ext'] = extension_loaded('redis');
    $checks['curl'] = extension_loaded('curl');
    $checks['gd'] = extension_loaded('gd');
    $checks['json'] = extension_loaded('json');
    $checks['mbstring'] = extension_loaded('mbstring');

    // 不检测 Webman 函数，因为 FPM 检测不准确
    // 在步骤2强制提示用户执行解禁命令
    $checks['webman_functions'] = true;
    $checks['need_fix_functions'] = true; // 标记需要执行解禁命令

    return $checks;
}

// 检查数据库连接
function checkDatabase()
{
    header('Content-Type: application/json');

    $host = $_POST['db_host'] ?? '';
    $port = $_POST['db_port'] ?? '3306';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';
    $name = $_POST['db_name'] ?? '';

    try {
        $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        if (version_compare($version, '8.0.0', '<')) {
            echo json_encode(['success' => false, 'error' => 'MySQL版本过低，需要8.0.0及以上版本']);
            exit;
        }

        $stmt = $pdo->query("SHOW DATABASES LIKE '$name'");
        $exists = $stmt->rowCount() > 0;

        echo json_encode(['success' => true, 'exists' => $exists, 'version' => $version]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => '数据库连接失败：' . $e->getMessage()]);
    }

    exit;
}

// 检查Redis连接
function checkRedis()
{
    header('Content-Type: application/json');

    $host = $_POST['redis_host'] ?? '127.0.0.1';
    $port = $_POST['redis_port'] ?? 6379;
    $pass = $_POST['redis_pass'] ?? '';
    $db = $_POST['redis_db'] ?? 0;

    try {
        if (!class_exists('Redis')) {
            echo json_encode(['success' => false, 'error' => 'Redis扩展未安装']);
            exit;
        }

        $redis = new Redis();
        if (!@$redis->connect($host, $port, 2)) {
            echo json_encode(['success' => false, 'error' => 'Redis连接失败']);
            exit;
        }

        if ($pass && !@$redis->auth($pass)) {
            echo json_encode(['success' => false, 'error' => 'Redis密码错误']);
            exit;
        }

        if (!@$redis->select($db)) {
            echo json_encode(['success' => false, 'error' => 'Redis数据库选择失败']);
            exit;
        }

        $testKey = 'install_test_' . time();
        $redis->set($testKey, '1', 10);
        $redis->del($testKey);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    exit;
}

// 显示已安装提示页面
function showAlreadyInstalled()
{
    $lockFile = '../../install.lock';
    $installTime = '';
    
    if (file_exists($lockFile)) {
        $installTime = file_get_contents($lockFile);
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>系统已安装 - FastMovie Admin</title>
        <link rel="stylesheet" href="assets/style.css">
        <style>
            .installed-container {
                max-width: 600px;
                margin: 100px auto;
                padding: 40px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            
            .installed-icon {
                font-size: 80px;
                margin-bottom: 20px;
            }
            
            .installed-title {
                font-size: 28px;
                color: #333;
                margin-bottom: 15px;
                font-weight: 700;
            }
            
            .installed-message {
                font-size: 16px;
                color: #666;
                line-height: 1.8;
                margin-bottom: 30px;
            }
            
            .installed-info {
                background: #f6ffed;
                border: 2px solid #52c41a;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 30px;
                text-align: left;
            }
            
            .installed-info h3 {
                color: #52c41a;
                margin: 0 0 15px 0;
                font-size: 16px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .installed-info p {
                margin: 8px 0;
                font-size: 14px;
                color: #666;
            }
            
            .installed-warning {
                background: #fff7e6;
                border: 2px solid #fa8c16;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 30px;
                text-align: left;
            }
            
            .installed-warning h3 {
                color: #fa8c16;
                margin: 0 0 15px 0;
                font-size: 16px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .installed-warning p {
                margin: 8px 0;
                font-size: 14px;
                color: #666;
                line-height: 1.8;
            }
            
            .installed-warning code {
                background: #f0f0f0;
                padding: 2px 8px;
                border-radius: 4px;
                color: #d4380d;
                font-family: 'Consolas', monospace;
            }
            
            .btn-group {
                display: flex;
                gap: 15px;
                justify-content: center;
            }
            
            .btn-admin {
                padding: 12px 30px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: transform 0.2s;
            }
            
            .btn-admin:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            }
            
            .btn-home {
                padding: 12px 30px;
                background: white;
                color: #667eea;
                border: 2px solid #667eea;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: all 0.2s;
            }
            
            .btn-home:hover {
                background: #667eea;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="installed-container">
            <div class="installed-icon">✅</div>
            <h1 class="installed-title">系统已安装</h1>
            <p class="installed-message">
                FastMovie Admin 已经成功安装<br>
                请勿重复安装
            </p>
            
            <?php if ($installTime): ?>
            <div class="installed-info">
                <h3><span>📅</span> 安装信息</h3>
                <p><strong>安装时间：</strong><?php echo htmlspecialchars($installTime); ?></p>
                <p><strong>后台地址：</strong>http://你的域名/admin</p>
            </div>
            <?php endif; ?>
            
            <div class="installed-warning">
                <h3><span>⚠️</span> 如需重新安装</h3>
                <p>
                    1. 删除根目录下的 <code>install.lock</code> 文件<br>
                    2. 清空数据库（可选）<br>
                    3. 重新访问安装向导
                </p>
                <p style="color: #ff4d4f; margin-top: 15px;">
                    <strong>⚠️ 警告：</strong>重新安装将清空所有数据，请谨慎操作！
                </p>
            </div>
            
            <div class="btn-group">
                <a href="../../admin" class="btn-admin">进入后台管理</a>
                <a href="../../" class="btn-home">返回首页</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

