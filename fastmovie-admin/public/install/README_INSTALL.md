# FastMovie Admin 安装说明

## 安装步骤

### 1. 访问安装向导

在浏览器中打开：
```
http://你的域名/install
```

### 2. 环境检测页面 - 重要操作

在**步骤2：环境检测**页面，会看到醒目的提示框。

**必须在服务器上执行以下命令**（需要 root 权限）：

```bash
curl -Ss https://www.workerman.net/webman/fix-disable-functions | php
```

**为什么必须执行？**
- Webman 运行在 CLI 模式，需要特定的 PHP 函数
- Web 安装器运行在 FPM 模式，无法准确检测 CLI 环境
- 不执行此命令，安装完成后 Webman 服务将无法启动

**执行后会自动：**
- 检测 CLI 环境的 php.ini 位置
- 自动解禁 Webman 所需的函数
- 无需手动修改配置文件

### 3. 继续安装

执行完解禁命令后，直接点击"下一步"继续：
- 配置数据库和 Redis
- 创建管理员账号
- 完成安装

### 4. 启动服务

```bash
cd /path/to/fastmovie-admin
php start.php start
```

## 常见问题

**Q: 为什么不能自动检测 CLI 环境？**  
A: Web 安装器运行在 FPM 进程中，使用的是 FPM 的 php.ini。而 Webman 运行在 CLI 进程中，使用的是 CLI 的 php.ini。两者是不同的配置文件。

**Q: 可以不执行解禁命令吗？**  
A: 不可以。如果不执行，安装完成后运行 `php start.php start` 会报错，提示函数被禁用。

**Q: 解禁命令安全吗？**  
A: 这是 Workerman 官方提供的脚本，只会解禁 Webman 运行所需的函数，不会影响系统安全。

**Q: 如果无法执行 curl 命令怎么办？**  
A: 可以手动修改 CLI 的 php.ini：
1. 查找 CLI 的 php.ini：`php --ini`
2. 编辑 php.ini，找到 `disable_functions`
3. 移除以下函数：stream_socket_server, stream_socket_client, pcntl_signal_dispatch, pcntl_signal, pcntl_alarm, pcntl_fork, posix_getuid, posix_getpwuid, posix_kill, posix_setsid, posix_getpid
4. 保存文件

## 启动服务

```bash
# 开发模式（前台运行）
php start.php start

# 生产模式（后台运行）
php start.php start -d

# 停止服务
php start.php stop

# 重启服务
php start.php restart

# 查看状态
php start.php status
```

## 访问后台

```
http://你的域名/admin
```

## 安装后清理

确认服务正常运行后，删除安装目录：

```bash
rm -rf /path/to/fastmovie-admin/public/install
```

## 技术支持

- Webman 官方文档：https://www.workerman.net/doc/webman/
- 项目地址：https://gitee.com/yc_open/ai-short-play
