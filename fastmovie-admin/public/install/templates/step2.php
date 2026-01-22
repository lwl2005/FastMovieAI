<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastMovie Admin 安装向导 - 环境检测</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="steps">
                <span class="step done" data-step="✓">许可协议</span>
                <span class="step active" data-step="②">环境检测</span>
                <span class="step" data-step="③">参数配置</span>
                <span class="step" data-step="④">安装</span>
            </div>
        </div>
        <div class="content">
            <h2>环境检测</h2>
            <table class="check-table">
                <thead>
                    <tr>
                        <th width="30%">检测项</th>
                        <th width="20%">要求</th>
                        <th width="30%">当前</th>
                        <th width="20%">状态</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>PHP版本</td>
                        <td><?php echo MIN_PHP_VERSION; ?> 及以上</td>
                        <td><?php echo PHP_VERSION; ?></td>
                        <td><?php echo $checks['php_version'] ? '<span class="success">✓ 通过</span>' : '<span class="error">✗ 不通过</span>'; ?></td>
                    </tr>
                    <tr>
                        <td>PDO扩展</td>
                        <td>必须开启</td>
                        <td><?php echo $checks['pdo'] ? '已开启' : '未开启'; ?></td>
                        <td><?php echo $checks['pdo'] ? '<span class="success">✓ 通过</span>' : '<span class="error">✗ 不通过</span>'; ?></td>
                    </tr>
                    <tr>
                        <td>MySQLi扩展</td>
                        <td>必须开启</td>
                        <td><?php echo $checks['mysqli'] ? '已开启' : '未开启'; ?></td>
                        <td><?php echo $checks['mysqli'] ? '<span class="success">✓ 通过</span>' : '<span class="error">✗ 不通过</span>'; ?></td>
                    </tr>
                    <tr>
                        <td>Redis扩展</td>
                        <td>必须开启</td>
                        <td><?php echo $checks['redis_ext'] ? '已开启' : '未开启'; ?></td>
                        <td><?php echo $checks['redis_ext'] ? '<span class="success">✓ 通过</span>' : '<span class="error">✗ 不通过</span>'; ?></td>
                    </tr>
                    <tr>
                        <td>cURL扩展</td>
                        <td>必须开启</td>
                        <td><?php echo $checks['curl'] ? '已开启' : '未开启'; ?></td>
                        <td><?php echo $checks['curl'] ? '<span class="success">✓ 通过</span>' : '<span class="error">✗ 不通过</span>'; ?></td>
                    </tr>
                    <tr>
                        <td>GD库</td>
                        <td>必须开启</td>
                        <td><?php echo $checks['gd'] ? '已开启' : '未开启'; ?></td>
                        <td><?php echo $checks['gd'] ? '<span class="success">✓ 通过</span>' : '<span class="error">✗ 不通过</span>'; ?></td>
                    </tr>
                    <tr>
                        <td>Webman必需函数</td>
                        <td>需要解禁</td>
                        <td>请执行解禁命令</td>
                        <td><span class="warning" style="color: #fa8c16;">⚠ 必须操作</span></td>
                    </tr>
                    <tr>
                        <td>SQL数据文件</td>
                        <td>必须存在</td>
                        <td><?php echo $sqlFileExists ? 'fastmovie-ai.sql' : '未找到'; ?></td>
                        <td><?php echo $sqlFileExists ? '<span class="success">✓ 通过</span>' : '<span class="error">✗ 不通过</span>'; ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="warning-box" style="background: #fff7e6; border-left: 4px solid #fa8c16;">
                <h3>⚠️ 重要：必须执行 Webman 函数解禁</h3>
                <p><strong>为什么需要执行？</strong></p>
                <ul style="padding-left: 25px; margin: 10px 0; line-height: 1.8;">
                    <li>Webman 框架运行在 <strong>CLI 模式</strong>，需要特定的 PHP 函数</li>
                    <li>当前 Web 安装器运行在 <strong>FPM 模式</strong>，无法准确检测 CLI 环境</li>
                    <li>如果不解禁，安装完成后 Webman 服务将<strong>无法启动</strong></li>
                </ul>
                
                <p style="margin-top: 15px;"><strong>请在服务器上执行以下命令（需要 root 权限）：</strong></p>
                <div style="background: #1e1e1e; color: #4ec9b0; padding: 15px; border-radius: 6px; margin: 15px 0; font-family: monospace; font-size: 14px; position: relative;">
                    <div style="position: absolute; top: 10px; right: 10px;">
                        <button onclick="copyCommand()" style="background: #4ec9b0; color: #1e1e1e; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">复制</button>
                    </div>
                    <div id="fixCommand">curl -Ss https://www.workerman.net/webman/fix-disable-functions | php</div>
                </div>
                
                <p style="margin-top: 15px;"><strong>执行后会自动：</strong></p>
                <ul style="padding-left: 25px; margin: 10px 0; line-height: 1.8;">
                    <li>检测 CLI 环境的 php.ini 位置</li>
                    <li>自动解禁 Webman 所需的函数</li>
                    <li>无需手动修改配置文件</li>
                </ul>
                
                <div style="background: #e6f7ff; border: 1px solid #91d5ff; padding: 12px; border-radius: 6px; margin-top: 15px;">
                    <strong style="color: #1890ff;">💡 提示：</strong>
                    <span style="color: #666;">执行完命令后，直接点击"下一步"继续安装即可，无需刷新页面。</span>
                </div>
            </div>

            <div class="warning-box" style="background: #fff1f0; border-left: 4px solid #ff4d4f; margin-top: 20px;">
                <h3>🔐 重要：设置目录权限</h3>
                <p><strong>为什么需要设置？</strong></p>
                <ul style="padding-left: 25px; margin: 10px 0; line-height: 1.8;">
                    <li>安装过程需要创建 <code>.env</code> 配置文件和 <code>install.lock</code> 锁定文件</li>
                    <li>Webman 运行时需要写入日志、缓存等文件</li>
                    <li>如果权限不足，安装将<strong>无法完成</strong></li>
                </ul>
                
                <p style="margin-top: 15px;"><strong>方法一：使用 SSH 命令（推荐）</strong></p>
                <div style="background: #1e1e1e; color: #4ec9b0; padding: 15px; border-radius: 6px; margin: 15px 0; font-family: monospace; font-size: 14px; position: relative;">
                    <div style="position: absolute; top: 10px; right: 10px;">
                        <button onclick="copyPermCommand()" style="background: #4ec9b0; color: #1e1e1e; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">复制</button>
                    </div>
                    <div id="permCommand"># 进入网站根目录<br>cd <?php echo ROOT_PATH; ?><br><br># 设置目录权限为 755<br>chmod -R 755 .<br><br># 设置所有者为 www 用户<br>chown -R www:www .</div>
                </div>
                
                <p style="margin-top: 15px;"><strong>方法二：使用宝塔面板</strong></p>
                <ol style="padding-left: 25px; margin: 10px 0; line-height: 1.8;">
                    <li>进入"文件"管理</li>
                    <li>找到网站根目录：<code><?php echo ROOT_PATH; ?></code></li>
                    <li>右键点击目录 → 选择"权限"</li>
                    <li>设置权限为 <strong>755</strong>，勾选"应用到子目录"</li>
                    <li>设置所有者为 <strong>www</strong>，勾选"应用到子目录"</li>
                </ol>
                
                <div style="background: #fff7e6; border: 1px solid #ffa940; padding: 12px; border-radius: 6px; margin-top: 15px;">
                    <strong style="color: #fa8c16;">⚠️ 注意：</strong>
                    <span style="color: #666;">权限设置为 755 即可，不建议设置为 777（安全风险）。确保所有者为 www 用户。</span>
                </div>
            </div>

            <script>
            function copyCommand() {
                const command = document.getElementById('fixCommand').textContent;
                navigator.clipboard.writeText(command).then(() => {
                    alert('命令已复制到剪贴板');
                }).catch(() => {
                    // 降级方案
                    const textarea = document.createElement('textarea');
                    textarea.value = command;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    alert('命令已复制到剪贴板');
                });
            }
            
            function copyPermCommand() {
                const command = document.getElementById('permCommand').innerHTML
                    .replace(/<br>/g, '\n')
                    .replace(/&nbsp;/g, ' ')
                    .replace(/<[^>]*>/g, '');
                navigator.clipboard.writeText(command).then(() => {
                    alert('命令已复制到剪贴板');
                }).catch(() => {
                    // 降级方案
                    const textarea = document.createElement('textarea');
                    textarea.value = command;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    alert('命令已复制到剪贴板');
                });
            }
            </script>

            <?php if (!$canContinue): ?>
                <div class="error-box">
                    <strong>⚠ 环境检测未通过</strong>
                    <p>请确保所有必需的PHP扩展都已安装并启用，然后刷新页面重新检测。</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="footer">
            <button class="btn" onclick="location.href='?step=1'">← 上一步</button>
            <button class="btn btn-primary" <?php echo $canContinue ? '' : 'disabled'; ?> onclick="location.href='?step=3'">
                下一步 →
            </button>
        </div>
    </div>
</body>

</html>