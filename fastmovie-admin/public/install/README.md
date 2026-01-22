# FastMovie Admin 安装向导

## 📋 安装说明

### 准备工作

1. **复制 SQL 文件**
   
   将 SQL 文件复制到安装目录：
   ```bash
   # Linux/Mac
   cp install/sql/fastmovie-ai.sql fastmovie-admin/public/install/
   
   # Windows
   copy install\sql\fastmovie-ai.sql fastmovie-admin\public\install\
   ```

### 环境要求

- PHP >= 7.4.0
- MySQL >= 5.6.0
- Redis
- PHP扩展：PDO、MySQLi、Redis、cURL、GD、JSON、MBString
- Webman 必需函数已解除禁用

### 安装步骤

1. **上传文件**
   - 将整个项目上传到服务器

2. **解除 PHP 函数禁用（重要！）**
   
   Webman 框架需要一些 PHP 函数才能正常运行，这些函数在某些服务器上可能被禁用。
   
   **快速检测：**
   访问 `http://你的域名/install/test.php` 可以快速检测环境是否满足要求。
   
   **方法一：使用官方脚本（推荐）**
   ```bash
   curl -Ss https://www.workerman.net/webman/fix-disable-functions | php
   ```
   
   **方法二：手动修改**
   - 编辑 `php.ini` 文件
   - 找到 `disable_functions` 配置项
   - 移除以下函数：
     ```
     stream_socket_server, stream_socket_client, pcntl_signal_dispatch,
     pcntl_signal, pcntl_alarm, pcntl_fork, posix_getuid, posix_getpwuid,
     posix_kill, posix_setsid, posix_getpid, posix_getpwnam, posix_getgrnam,
     posix_getgid, posix_setgid, posix_initgroups, posix_setuid, posix_isatty
     ```
   - 重启 PHP-FPM 或 Web 服务器
   
   **验证：**
   ```bash
   php -r "echo function_exists('pcntl_fork') ? 'OK' : 'FAIL';"
   ```
   如果输出 `OK` 则表示成功。

3. **配置权限**
   ```bash
   chmod -R 755 fastmovie-admin
   chmod -R 777 fastmovie-admin/runtime
   ```

4. **访问安装向导**
   - 浏览器访问：`http://你的域名/install/`
   - 按照向导提示完成安装

5. **安装步骤说明**
   - **步骤1**：阅读并同意许可协议
   - **步骤2**：环境检测，确保所有必需扩展已安装（会自动检测 Webman 函数）
   - **步骤3**：配置数据库、Redis和管理员信息
   - **步骤4**：自动执行安装
   - **步骤5**：安装完成

6. **启动服务**
   ```bash
   cd fastmovie-admin
   php start.php start
   ```
   
   **开发模式（调试）：**
   ```bash
   php start.php start -d
   ```
   
   **停止服务：**
   ```bash
   php start.php stop
   ```
   
   **重启服务：**
   ```bash
   php start.php restart
   ```

7. **访问后台**
   - 后台地址：`http://你的域名/admin`
   - 使用安装时设置的管理员账号登录

### ⚠️ 安全提示

安装完成后，请务必：

1. **删除安装目录**
   ```bash
   rm -rf fastmovie-admin/public/install
   ```

2. **修改配置文件权限**
   ```bash
   chmod 644 fastmovie-admin/.env
   ```

3. **修改管理员密码**
   - 首次登录后立即修改默认密码

### 🔧 常见问题

**Q: 环境检测提示"Webman必需函数被禁用"？**
A: 执行命令 `curl -Ss https://www.workerman.net/webman/fix-disable-functions | php` 解除函数禁用，然后重启 PHP-FPM。

**Q: 安装时提示"Redis扩展未安装"？**
A: 需要安装PHP的Redis扩展，可以使用 `pecl install redis` 或通过包管理器安装。

**Q: 数据库连接失败？**
A: 检查数据库地址、端口、用户名和密码是否正确，确保MySQL服务正在运行。

**Q: 安装完成后无法访问后台？**
A: 确保已启动Webman服务（`php start.php start`），检查端口是否被占用。如果是生产环境，建议使用 `-d` 参数以守护进程模式运行。

**Q: 如何重新安装？**
A: 删除根目录下的 `install.lock` 文件，然后重新访问安装向导。

**Q: Webman 服务启动失败？**
A: 
1. 检查是否已解除函数禁用
2. 检查端口是否被占用（默认36999）
3. 查看错误日志：`tail -f runtime/logs/workerman.log`
4. 确保 Redis 服务正常运行

**Q: 如何在生产环境部署？**
A:
1. 使用守护进程模式：`php start.php start -d`
2. 配置 Nginx 反向代理
3. 设置开机自启动
4. 定期备份数据库和配置文件

### 📞 技术支持

如遇到问题，请联系技术支持团队。

---

**版本**: 1.0.0  
**更新时间**: 2026-01-20
