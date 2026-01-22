<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastMovie Admin 安装向导 - 参数配置</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .config-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            border-bottom: 2px solid #e8e8e8;
        }

        .config-tab {
            padding: 12px 25px;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 15px;
            color: #666;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            margin-bottom: -2px;
        }

        .config-tab:hover {
            color: #1890ff;
        }

        .config-tab.active {
            color: #1890ff;
            border-bottom-color: #1890ff;
            font-weight: 600;
        }

        .config-tab.completed {
            color: #52c41a;
        }

        .config-tab.completed::after {
            content: "✓";
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            font-size: 14px;
            color: #52c41a;
        }

        .config-tab.error {
            color: #ff4d4f;
        }

        .config-tab.error::after {
            content: "✗";
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            font-size: 14px;
            color: #ff4d4f;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-section {
            margin-bottom: 0;
        }

        .validation-msg {
            display: inline-block;
            margin-left: 10px;
            font-size: 12px;
            color: #999;
        }

        .validation-msg.success {
            color: #52c41a;
        }

        .validation-msg.error {
            color: #ff4d4f;
        }

        .validation-msg.loading {
            color: #1890ff;
        }

        .tab-actions {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="steps">
                <span class="step done" data-step="✓">许可协议</span>
                <span class="step done" data-step="✓">环境检测</span>
                <span class="step active" data-step="③">参数配置</span>
                <span class="step" data-step="④">安装</span>
            </div>
        </div>
        <div class="content">
            <h2>参数配置</h2>

            <div class="config-tabs">
                <button class="config-tab active" data-tab="database" onclick="switchTab('database')">
                    数据库配置
                </button>
                <button class="config-tab" data-tab="redis" onclick="switchTab('redis')">
                    Redis配置
                </button>
                <button class="config-tab" data-tab="admin" onclick="switchTab('admin')">
                    管理员配置
                </button>
            </div>

            <form id="configForm" method="post" action="?step=4">
                <!-- 数据库配置 -->
                <div class="tab-content active" id="tab-database">
                    <div class="form-section">
                        <div class="form-row">
                            <div class="form-group">
                                <label>数据库主机 *</label>
                                <input type="text" name="db_host" id="db_host" value="127.0.0.1" required>
                                <span class="tip">通常为 127.0.0.1 或 localhost</span>
                            </div>
                            <div class="form-group">
                                <label>数据库端口 *</label>
                                <input type="text" name="db_port" id="db_port" value="3306" required>
                                <span class="tip">MySQL默认端口为 3306</span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>数据库用户名 *</label>
                                <input type="text" name="db_user" id="db_user" value="root" required>
                            </div>
                            <div class="form-group">
                                <label>数据库密码 *</label>
                                <input type="password" name="db_pass" id="db_pass" required onblur="validateDatabase()">
                                <span id="dbStatus"></span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>数据库名 *</label>
                                <input type="text" name="db_name" id="db_name" value="fastmovie" required>
                                <span class="tip">如不存在将自动创建</span>
                            </div>
                            <div class="form-group">
                                <label>表前缀</label>
                                <input type="text" name="db_prefix" id="db_prefix" value="php_">
                                <span class="tip">建议使用默认值</span>
                            </div>
                        </div>
                    </div>
                    <div class="tab-actions">
                        <div></div>
                        <button type="button" class="btn btn-primary" onclick="nextTab('redis')">下一步 →</button>
                    </div>
                </div>

                <!-- Redis配置 -->
                <div class="tab-content" id="tab-redis">
                    <div class="form-section">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Redis主机 *</label>
                                <input type="text" name="redis_host" id="redis_host" value="127.0.0.1" required>
                            </div>
                            <div class="form-group">
                                <label>Redis端口 *</label>
                                <input type="text" name="redis_port" id="redis_port" value="6379" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Redis密码</label>
                                <input type="password" name="redis_pass" id="redis_pass" placeholder="如无密码请留空" onblur="validateRedis()">
                                <span id="redisStatus"></span>
                            </div>
                            <div class="form-group">
                                <label>Redis数据库</label>
                                <input type="text" name="redis_db" id="redis_db" value="0">
                                <span class="tip">默认为 0</span>
                            </div>
                        </div>
                    </div>
                    <div class="tab-actions">
                        <button type="button" class="btn" onclick="switchTab('database')">← 上一步</button>
                        <button type="button" class="btn btn-primary" onclick="nextTab('admin')">下一步 →</button>
                    </div>
                </div>

                <!-- 管理员配置 -->
                <div class="tab-content" id="tab-admin">
                    <div class="form-section">
                        <div class="form-row">
                            <div class="form-group">
                                <label>管理员账号 *</label>
                                <input type="text" name="admin_user" id="admin_user" value="admin" required>
                                <span class="tip">用于登录后台</span>
                            </div>
                            <div class="form-group">
                                <label>管理员昵称 *</label>
                                <input type="text" name="admin_nickname" id="admin_nickname" value="超级管理员" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>管理员密码 *</label>
                                <input type="password" name="admin_pass" id="admin_pass" required minlength="6">
                                <span class="tip">至少6位字符</span>
                            </div>
                            <div class="form-group">
                                <label>确认密码 *</label>
                                <input type="password" name="admin_pass2" id="admin_pass2" required minlength="6" onblur="validatePassword()">
                                <span id="passStatus"></span>
                            </div>
                        </div>
                    </div>
                    <div class="tab-actions">
                        <button type="button" class="btn" onclick="switchTab('redis')">← 上一步</button>
                        <button type="button" class="btn btn-primary" onclick="submitForm()">完成配置 →</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="footer">
            <button class="btn" onclick="location.href='?step=2'">← 返回环境检测</button>
        </div>
    </div>

    <script>
        let validationStatus = {
            database: false,
            redis: false,
            admin: false
        };

        function switchTab(tabName) {
            // 隐藏所有标签内容
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.config-tab').forEach(el => el.classList.remove('active'));

            // 显示选中的标签
            document.getElementById('tab-' + tabName).classList.add('active');
            document.querySelector('[data-tab="' + tabName + '"]').classList.add('active');
        }

        function nextTab(tabName) {
            // 验证当前标签
            const currentTab = document.querySelector('.config-tab.active').dataset.tab;

            if (currentTab === 'database') {
                // 触发数据库验证
                validateDatabase();

                // 等待验证完成后检查状态
                setTimeout(() => {
                    if (!validationStatus.database) {
                        alert('请先完成数据库配置验证');
                        return;
                    }
                    switchTab(tabName);
                }, 100);
                return;
            }

            if (currentTab === 'redis') {
                // 触发Redis验证
                validateRedis();

                // 等待验证完成后检查状态
                setTimeout(() => {
                    if (!validationStatus.redis) {
                        alert('请先完成Redis配置验证');
                        return;
                    }
                    switchTab(tabName);
                }, 100);
                return;
            }

            switchTab(tabName);
        }

        function validateDatabase() {
            const host = document.getElementById('db_host').value;
            const port = document.getElementById('db_port').value;
            const user = document.getElementById('db_user').value;
            const pass = document.getElementById('db_pass').value;
            const name = document.getElementById('db_name').value;

            if (!host || !port || !user || !pass || !name) {
                return;
            }

            const status = document.getElementById('dbStatus');
            const tab = document.querySelector('[data-tab="database"]');
            status.innerHTML = '<span class="validation-msg loading">验证中...</span>';

            const formData = new FormData();
            formData.append('db_host', host);
            formData.append('db_port', port);
            formData.append('db_user', user);
            formData.append('db_pass', pass);
            formData.append('db_name', name);

            fetch('?step=3&check_db=1', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        status.innerHTML = '<span class="validation-msg success">连接成功</span>';
                        tab.classList.remove('error');
                        tab.classList.add('completed');
                        validationStatus.database = true;
                    } else {
                        status.innerHTML = '<span class="validation-msg error">' + data.error + '</span>';
                        tab.classList.remove('completed');
                        tab.classList.add('error');
                        validationStatus.database = false;
                    }
                })
                .catch(err => {
                    status.innerHTML = '<span class="validation-msg error">请求失败</span>';
                    tab.classList.remove('completed');
                    tab.classList.add('error');
                    validationStatus.database = false;
                });
        }

        function validateRedis() {
            const host = document.getElementById('redis_host').value;
            const port = document.getElementById('redis_port').value;
            const pass = document.getElementById('redis_pass').value;
            const db = document.getElementById('redis_db').value;

            if (!host || !port) {
                return;
            }

            const status = document.getElementById('redisStatus');
            const tab = document.querySelector('[data-tab="redis"]');
            status.innerHTML = '<span class="validation-msg loading">验证中...</span>';

            const formData = new FormData();
            formData.append('redis_host', host);
            formData.append('redis_port', port);
            formData.append('redis_pass', pass);
            formData.append('redis_db', db);

            fetch('?step=3&check_redis=1', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        status.innerHTML = '<span class="validation-msg success">连接成功</span>';
                        tab.classList.remove('error');
                        tab.classList.add('completed');
                        validationStatus.redis = true;
                    } else {
                        status.innerHTML = '<span class="validation-msg error">' + data.error + '</span>';
                        tab.classList.remove('completed');
                        tab.classList.add('error');
                        validationStatus.redis = false;
                    }
                })
                .catch(err => {
                    status.innerHTML = '<span class="validation-msg error">请求失败</span>';
                    tab.classList.remove('completed');
                    tab.classList.add('error');
                    validationStatus.redis = false;
                });
        }

        function validatePassword() {
            const pass1 = document.getElementById('admin_pass').value;
            const pass2 = document.getElementById('admin_pass2').value;
            const status = document.getElementById('passStatus');
            const tab = document.querySelector('[data-tab="admin"]');

            if (!pass2) {
                return;
            }

            if (pass1 !== pass2) {
                status.innerHTML = '<span class="validation-msg error">两次密码不一致</span>';
                tab.classList.remove('completed');
                tab.classList.add('error');
                validationStatus.admin = false;
                return false;
            }

            if (pass1.length < 6) {
                status.innerHTML = '<span class="validation-msg error">密码至少6位</span>';
                tab.classList.remove('completed');
                tab.classList.add('error');
                validationStatus.admin = false;
                return false;
            }

            status.innerHTML = '<span class="validation-msg success">密码验证通过</span>';
            tab.classList.remove('error');
            tab.classList.add('completed');
            validationStatus.admin = true;
            return true;
        }

        function submitForm() {
            const form = document.getElementById('configForm');

            // 验证所有必填项
            if (!validationStatus.database) {
                alert('请先完成数据库配置验证');
                switchTab('database');
                return false;
            }

            if (!validationStatus.redis) {
                alert('请先完成Redis配置验证');
                switchTab('redis');
                return false;
            }

            if (!validatePassword()) {
                alert('请检查管理员密码');
                return false;
            }

            form.submit();
        }
    </script>
</body>

</html>