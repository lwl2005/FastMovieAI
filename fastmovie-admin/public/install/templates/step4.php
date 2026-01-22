<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastMovie Admin 安装向导 - 安装</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .content {
            padding: 25px 40px !important;
        }

        .content h2 {
            margin-bottom: 15px !important;
            font-size: 20px !important;
        }

        .install-container {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 20px;
            margin-top: 10px;
            align-items: start;
        }

        .install-left {
            background: #fafafa;
            border-radius: 10px;
            padding: 20px;
            color: #333;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e8e8e8;
        }

        .install-left h3 {
            color: #333;
            margin: 0 0 12px 0;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .config-card {
            background: #fff;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            border: 1px solid #e8e8e8;
        }

        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .config-item:last-child {
            border-bottom: none;
        }

        .config-label {
            color: #666;
            font-size: 12px;
        }

        .config-value {
            color: #333;
            font-weight: 600;
            font-size: 12px;
        }

        .progress-section {
            margin-top: 12px;
        }

        .progress-bar-new {
            width: 100%;
            height: 6px;
            background: #e8e8e8;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .progress-new {
            height: 100%;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            transition: width 0.3s ease;
            width: 0;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(79, 172, 254, 0.5);
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }

        .progress-percent {
            font-size: 20px;
            font-weight: 700;
            color: #1890ff;
        }

        .progress-status {
            color: #666;
            font-size: 12px;
        }

        .install-steps {
            margin-top: 12px;
        }

        .install-step-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            color: #999;
            font-size: 13px;
        }

        .install-step-item.active {
            color: #1890ff;
            font-weight: 600;
        }

        .install-step-item.done {
            color: #52c41a;
        }

        .step-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e8e8e8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
            color: #999;
        }

        .install-step-item.active .step-icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            box-shadow: 0 0 15px rgba(79, 172, 254, 0.6);
            animation: pulse 1.5s ease-in-out infinite;
        }

        .install-step-item.done .step-icon {
            background: #52c41a;
            color: white;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .install-right {
            background: #1e1e1e;
            border-radius: 10px;
            padding: 0;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 420px;
            height: 100%;
        }

        .log-header {
            background: #2d2d2d;
            padding: 10px 15px;
            border-bottom: 1px solid #3d3d3d;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .log-header-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ff5f56;
        }

        .log-header-dot:nth-child(2) {
            background: #ffbd2e;
        }

        .log-header-dot:nth-child(3) {
            background: #27c93f;
        }

        .log-title {
            color: #999;
            font-size: 12px;
            margin-left: 8px;
            font-family: 'Consolas', monospace;
        }

        .install-log-new {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.6;
        }

        .install-log-new p {
            margin: 0;
            padding: 3px 0;
            color: #d4d4d4;
        }

        .install-log-new p.success {
            color: #4ec9b0;
        }

        .install-log-new p.error {
            color: #f48771;
        }

        .install-log-new p.info {
            color: #9cdcfe;
        }

        .install-log-new p.sql {
            color: #ce9178;
            font-size: 11px;
            padding-left: 15px;
            opacity: 0.8;
        }

        .install-log-new p.sql::before {
            content: "→ ";
            color: #569cd6;
            margin-right: 5px;
        }

        .info-box {
            margin: 0 !important;
            padding: 15px !important;
            background: #fafafa;
            border-radius: 10px;
            border: 1px solid #e8e8e8;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .info-box h3 {
            margin-bottom: 10px !important;
            font-size: 14px !important;
            color: #333;
        }

        .info-box ul {
            margin-top: 8px !important;
            flex: 1;
        }

        .info-box li {
            padding: 4px 0 !important;
            font-size: 13px !important;
            color: #666;
        }

        .footer {
            padding: 15px 40px !important;
        }

        @media (max-width: 1024px) {
            .install-container {
                grid-template-columns: 1fr;
            }

            .install-left {
                order: 2;
            }

            .install-right {
                order: 1;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="steps">
                <span class="step done" data-step="✓">许可协议</span>
                <span class="step done" data-step="✓">环境检测</span>
                <span class="step done" data-step="✓">参数配置</span>
                <span class="step active" data-step="④" id="stepInstalling">安装中</span>
                <span class="step" data-step="⑤" id="stepComplete">确认信息</span>
            </div>
        </div>
        <div class="content">
            <h2 id="pageTitle">开始安装</h2>

            <div id="beforeInstall">
                <div class="install-container">
                    <div class="install-left">
                        <h3>配置信息</h3>
                        <div class="config-card">
                            <div class="config-item">
                                <span class="config-label">数据库地址</span>
                                <span class="config-value"><?php echo $_SESSION['install_config']['db_host']; ?>:<?php echo $_SESSION['install_config']['db_port']; ?></span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">数据库名</span>
                                <span class="config-value"><?php echo $_SESSION['install_config']['db_name']; ?></span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">表前缀</span>
                                <span class="config-value"><?php echo $_SESSION['install_config']['db_prefix']; ?></span>
                            </div>
                            <div class="config-item">
                                <span class="config-label">管理员账号</span>
                                <span class="config-value"><?php echo $_SESSION['install_config']['admin_user']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="info-box">
                        <h3>💡 温馨提示</h3>
                        <ul style="padding-left: 20px;">
                            <li>安装过程需要几分钟时间，请耐心等待</li>
                            <li>安装过程中请勿关闭浏览器或刷新页面</li>
                            <li>如遇到超时问题，可使用命令行安装</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div id="installSection" style="display: none;">
                <div class="install-container" style="grid-template-columns: 280px 1fr;">
                    <!-- 左侧：进度和步骤 -->
                    <div class="install-left">
                        <div class="progress-section" style="margin-top: 0;">
                            <h3>安装进度</h3>
                            <div class="progress-bar-new">
                                <div class="progress-new" id="progressBar"></div>
                            </div>
                            <div class="progress-info">
                                <span class="progress-percent" id="progressPercent">0%</span>
                                <span class="progress-status" id="progressStatus">准备中...</span>
                            </div>
                        </div>

                        <div class="install-steps">
                            <div class="install-step-item" id="step1">
                                <span class="step-icon">1</span>
                                <span>连接数据库</span>
                            </div>
                            <div class="install-step-item" id="step2">
                                <span class="step-icon">2</span>
                                <span>创建数据库</span>
                            </div>
                            <div class="install-step-item" id="step3">
                                <span class="step-icon">3</span>
                                <span>导入SQL文件</span>
                            </div>
                            <div class="install-step-item" id="step4">
                                <span class="step-icon">4</span>
                                <span>创建管理员</span>
                            </div>
                            <div class="install-step-item" id="step5">
                                <span class="step-icon">5</span>
                                <span>生成配置文件</span>
                            </div>
                        </div>

                        <!-- 安装状态标签 -->
                        <div id="installStatusLabel" style="margin-top: 20px; padding: 12px; background: #e6f7ff; border-left: 4px solid #1890ff; border-radius: 4px; display: none;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">⏳</span>
                                <strong style="color: #1890ff; font-size: 13px;">安装中...</strong>
                            </div>
                        </div>

                        <div id="completeStatusLabel" style="margin-top: 20px; padding: 12px; background: #f6ffed; border-left: 4px solid #52c41a; border-radius: 4px; display: none;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">✅</span>
                                <strong style="color: #52c41a; font-size: 13px;">已完成</strong>
                            </div>
                        </div>
                    </div>

                    <!-- 右侧：实时日志 -->
                    <div class="install-right" id="installLogPanel">
                        <div class="log-header">
                            <span class="log-header-dot"></span>
                            <span class="log-header-dot"></span>
                            <span class="log-header-dot"></span>
                            <span class="log-title">install.log</span>
                        </div>
                        <div class="install-log-new" id="installLog">
                            <p class="info">等待开始...</p>
                        </div>
                    </div>

                    <!-- 右侧：完成信息和重要提示（安装完成后显示） -->
                    <div id="importantTipsPanel" style="display: none; background: #fafafa; border-radius: 10px; padding: 20px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); border: 1px solid #e8e8e8; overflow-y: auto;">
                        <!-- 安装完成卡片 -->
                        <div style="padding: 20px; background: linear-gradient(135deg, #f6ffed 0%, #e6fffb 100%); border: 2px solid #52c41a; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 4px 12px rgba(82, 196, 26, 0.15);">
                            <h3 style="color: #52c41a; margin: 0 0 12px 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 24px;">🎉</span> 安装完成！
                            </h3>
                            <p style="margin: 0; font-size: 13px; color: #666; line-height: 1.8;">
                                管理员账号：<strong style="color: #1890ff;"><?php echo $_SESSION['install_config']['admin_user']; ?></strong><br>
                                后台地址：<strong style="color: #1890ff;">http://你的域名/admin</strong>
                            </p>
                        </div>

                        <!-- 重要提示 -->
                        <div style="padding: 20px; background: #fff; border: 2px solid #ff4d4f; border-radius: 10px; box-shadow: 0 4px 12px rgba(255, 77, 79, 0.15);">
                            <h3 style="color: #ff4d4f; margin: 0 0 15px 0; font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 20px;">⚠️</span> 重要！请完成以下步骤
                            </h3>
                            
                            <!-- 步骤 1 -->
                            <div style="margin-bottom: 15px; padding: 12px; background: #fafafa; border-left: 4px solid #1890ff; border-radius: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #1890ff; color: white; border-radius: 50%; font-size: 12px; font-weight: 700;">1</span>
                                    <strong style="color: #1890ff; font-size: 13px;">配置伪静态规则</strong>
                                </div>
                                <p style="margin: 0 0 0 32px; font-size: 12px; color: #666; line-height: 1.6;">
                                    复制 <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px; color: #d4380d;">nginx.example</code> 文件内容<br>
                                    粘贴到宝塔面板"站点设置 → 伪静态"中并保存
                                </p>
                            </div>
                            
                            <!-- 步骤 2 -->
                            <div style="margin-bottom: 15px; padding: 12px; background: #fafafa; border-left: 4px solid #52c41a; border-radius: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #52c41a; color: white; border-radius: 50%; font-size: 12px; font-weight: 700;">2</span>
                                    <strong style="color: #52c41a; font-size: 13px;">启动后端服务</strong>
                                </div>
                                <p style="margin: 0 0 0 32px; font-size: 12px; color: #666; line-height: 1.6;">
                                    执行命令：<code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px; color: #d4380d;">php start.php start -d</code><br>
                                    或在宝塔面板配置进程守护
                                </p>
                            </div>
                            
                            <!-- 步骤 3 -->
                            <div style="padding: 12px; background: #fff2e8; border-left: 4px solid #fa8c16; border-radius: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #fa8c16; color: white; border-radius: 50%; font-size: 12px; font-weight: 700;">3</span>
                                    <strong style="color: #fa8c16; font-size: 13px;">删除安装目录</strong>
                                </div>
                                <p style="margin: 0 0 0 32px; font-size: 12px; color: #666; line-height: 1.6;">
                                    删除 <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px; color: #d4380d;">public/install</code> 目录<br>
                                    <span style="color: #ff4d4f; font-weight: 600;">⚠️ 这是安全必需步骤！</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="errorSection" style="display: none;">
                <div class="error-box">
                    <strong>⚠ 安装失败</strong>
                    <p id="errorMessage"></p>
                </div>

                <div class="warning-box">
                    <h3>💡 备用方案</h3>
                    <p>使用命令行安装：</p>
                    <div style="background: #1e1e1e; color: #4ec9b0; padding: 20px; border-radius: 6px; margin: 15px 0; font-family: monospace; font-size: 14px;">
                        cd <?php echo ROOT_PATH; ?>public/install<br>
                        php cli_install.php
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">
            <button class="btn" id="btnBack" onclick="location.href='?step=3'">← 上一步</button>
            <button class="btn btn-primary" id="btnInstall" onclick="startInstall()">🚀 开始安装</button>
            <button class="btn btn-primary" id="btnFinish" style="display: none;" onclick="confirmBeforeEnter()">进入后台 →</button>
        </div>
    </div>

    <!-- 确认弹窗 -->
    <div id="confirmModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.6); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 12px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);">
            <h3 style="margin: 0 0 20px 0; font-size: 18px; color: #333; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 28px;">⚠️</span> 确认配置完成
            </h3>
            <div style="margin-bottom: 25px; padding: 15px; background: #fff7e6; border-left: 4px solid #fa8c16; border-radius: 4px;">
                <p style="margin: 0 0 12px 0; font-size: 14px; color: #666; line-height: 1.8;">
                    在进入后台之前，请确认您已完成以下操置：
                </p>
                <div style="font-size: 13px; color: #666; line-height: 2;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 0;">
                        <input type="checkbox" id="check1" style="width: 16px; height: 16px; cursor: pointer;">
                        <span>✓ 已配置伪静态规则（nginx.example）</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 0;">
                        <input type="checkbox" id="check2" style="width: 16px; height: 16px; cursor: pointer;">
                        <span>✓ 已启动后端服务（php start.php start -d）</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 0;">
                        <input type="checkbox" id="check3" style="width: 16px; height: 16px; cursor: pointer;">
                        <span>✓ 已删除安装目录（public/install）</span>
                    </label>
                </div>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button onclick="closeModal()" style="padding: 10px 24px; border: 1px solid #d9d9d9; background: white; border-radius: 6px; cursor: pointer; font-size: 14px; color: #666;">
                    取消
                </button>
                <button id="confirmBtn" onclick="checkAndEnter()" style="padding: 10px 24px; border: none; background: #1890ff; color: white; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">
                    确认并进入
                </button>
            </div>
        </div>
    </div>

    <script>
        let isInstalling = false;
        let currentStep = 0;

        function startInstall() {
            if (isInstalling) return;
            isInstalling = true;

            document.getElementById('btnInstall').style.display = 'none';
            document.getElementById('btnBack').disabled = true;
            document.getElementById('beforeInstall').style.display = 'none';
            document.getElementById('installSection').style.display = 'block';

            // 显示"安装中"标签
            document.getElementById('installStatusLabel').style.display = 'block';

            const logDiv = document.getElementById('installLog');
            const progressBar = document.getElementById('progressBar');
            const progressPercent = document.getElementById('progressPercent');
            const progressStatus = document.getElementById('progressStatus');

            logDiv.innerHTML = '';

            const eventSource = new EventSource('?step=4&install=1');

            let progress = 0;

            eventSource.onmessage = function(event) {
                // 跳过空数据
                if (!event.data || event.data.trim() === '') {
                    return;
                }
                
                let data;
                try {
                    data = JSON.parse(event.data);
                } catch (e) {
                    console.error('JSON 解析错误:', event.data);
                    return;
                }

                if (data.type === 'done') {
                    eventSource.close();
                    progressBar.style.width = '100%';
                    progressPercent.textContent = '100%';
                    progressStatus.textContent = '安装完成';
                    markStepDone(5);

                    // 修改标题
                    document.getElementById('pageTitle').textContent = '已完成安装';

                    // 更新顶部步骤标签
                    const stepInstalling = document.getElementById('stepInstalling');
                    const stepComplete = document.getElementById('stepComplete');
                    if (stepInstalling) {
                        stepInstalling.classList.remove('active');
                        stepInstalling.classList.add('done');
                        stepInstalling.setAttribute('data-step', '✓');
                    }
                    if (stepComplete) {
                        stepComplete.classList.add('active');
                    }

                    // 隐藏日志面板，显示重要提示面板
                    document.getElementById('installLogPanel').style.display = 'none';
                    document.getElementById('importantTipsPanel').style.display = 'block';

                    // 同步高度
                    setTimeout(syncHeight, 100);

                    // 修改按钮
                    document.getElementById('btnInstall').style.display = 'none';
                    document.getElementById('btnFinish').style.display = 'inline-block';
                    return;
                }

                const p = document.createElement('p');
                p.className = data.type;
                p.textContent = data.message;
                logDiv.appendChild(p);
                
                // 滚动到底部显示最新日志
                logDiv.scrollTop = logDiv.scrollHeight;

                // 更新步骤状态
                if (data.message.includes('[1/5]')) {
                    markStepActive(1);
                    progressStatus.textContent = '连接数据库...';
                } else if (data.message.includes('[2/5]')) {
                    markStepDone(1);
                    markStepActive(2);
                    progressStatus.textContent = '创建数据库...';
                } else if (data.message.includes('[3/5]')) {
                    markStepDone(2);
                    markStepActive(3);
                    progressStatus.textContent = '导入SQL文件...';
                } else if (data.message.includes('[4/5]')) {
                    markStepDone(3);
                    markStepActive(4);
                    progressStatus.textContent = '创建管理员...';
                    
                    // SQL 导入完成，切换到"已完成"标签
                    document.getElementById('installStatusLabel').style.display = 'none';
                    document.getElementById('completeStatusLabel').style.display = 'block';
                    
                    // 同步高度
                    setTimeout(syncHeight, 100);
                } else if (data.message.includes('[5/5]')) {
                    markStepDone(4);
                    markStepActive(5);
                    progressStatus.textContent = '生成配置...';
                }

                if (data.type === 'success' || data.type === 'info' || data.type === 'sql') {
                    progress += 0.5;
                    if (progress > 95) progress = 95;
                    progressBar.style.width = progress + '%';
                    progressPercent.textContent = Math.floor(progress) + '%';
                }

                if (data.type === 'error') {
                    eventSource.close();
                    document.getElementById('installSection').style.display = 'none';
                    document.getElementById('errorSection').style.display = 'block';
                    document.getElementById('errorMessage').textContent = data.message;
                    document.getElementById('btnBack').disabled = false;
                }
            };

            eventSource.onerror = function() {
                if (!document.getElementById('successSection').style.display || document.getElementById('successSection').style.display === 'none') {
                    eventSource.close();
                    const p = document.createElement('p');
                    p.className = 'error';
                    p.textContent = '❌ 连接中断，可能是服务器超时';
                    logDiv.appendChild(p);

                    document.getElementById('installSection').style.display = 'none';
                    document.getElementById('errorSection').style.display = 'block';
                    document.getElementById('errorMessage').textContent = '安装过程中连接中断，请使用命令行安装';
                    document.getElementById('btnBack').disabled = false;
                }
            };
        }

        function markStepActive(step) {
            const stepEl = document.getElementById('step' + step);
            if (stepEl) {
                stepEl.classList.add('active');
            }
        }

        function markStepDone(step) {
            const stepEl = document.getElementById('step' + step);
            if (stepEl) {
                stepEl.classList.remove('active');
                stepEl.classList.add('done');
                stepEl.querySelector('.step-icon').textContent = '✓';
            }
        }

        // 同步左右两侧高度
        function syncHeight() {
            const leftPanel = document.querySelector('.install-left');
            const installLogPanel = document.getElementById('installLogPanel');
            const importantTipsPanel = document.getElementById('importantTipsPanel');
            
            if (leftPanel) {
                // 获取左侧实际高度
                const leftHeight = leftPanel.offsetHeight;
                
                // 同步日志面板高度（如果显示）
                if (installLogPanel && installLogPanel.style.display !== 'none') {
                    installLogPanel.style.height = leftHeight + 'px';
                    
                    // 确保日志滚动到底部
                    const logDiv = document.getElementById('installLog');
                    if (logDiv) {
                        logDiv.scrollTop = logDiv.scrollHeight;
                    }
                }
                
                // 同步重要提示面板高度（如果显示）
                if (importantTipsPanel && importantTipsPanel.style.display !== 'none') {
                    // 使用 min-height 确保内容完整显示
                    importantTipsPanel.style.minHeight = leftHeight + 'px';
                }
            }
        }

        // 页面加载完成后同步高度
        window.addEventListener('load', syncHeight);
        
        // 窗口大小改变时重新同步
        window.addEventListener('resize', syncHeight);
        
        // 使用 ResizeObserver 监听左侧面板高度变化
        if (typeof ResizeObserver !== 'undefined') {
            const leftPanel = document.querySelector('.install-left');
            if (leftPanel) {
                const resizeObserver = new ResizeObserver(() => {
                    syncHeight();
                });
                resizeObserver.observe(leftPanel);
            }
        }
        
        // 监听 importantTipsPanel 的显示状态变化
        const importantTipsPanel = document.getElementById('importantTipsPanel');
        if (importantTipsPanel) {
            const tipsObserver = new MutationObserver(() => {
                // 延迟执行以确保 DOM 已更新
                setTimeout(syncHeight, 50);
            });
            tipsObserver.observe(importantTipsPanel, { 
                attributes: true, 
                attributeFilter: ['style'],
                childList: true,
                subtree: true
            });
        }

        // 确认弹窗相关函数
        function confirmBeforeEnter() {
            const modal = document.getElementById('confirmModal');
            modal.style.display = 'flex';
            // 重置复选框
            document.getElementById('check1').checked = false;
            document.getElementById('check2').checked = false;
            document.getElementById('check3').checked = false;
            updateConfirmButton();
        }

        function closeModal() {
            document.getElementById('confirmModal').style.display = 'none';
        }

        function updateConfirmButton() {
            const check1 = document.getElementById('check1').checked;
            const check2 = document.getElementById('check2').checked;
            const check3 = document.getElementById('check3').checked;
            const confirmBtn = document.getElementById('confirmBtn');
            
            if (check1 && check2 && check3) {
                confirmBtn.style.background = '#52c41a';
                confirmBtn.style.cursor = 'pointer';
                confirmBtn.disabled = false;
            } else {
                confirmBtn.style.background = '#d9d9d9';
                confirmBtn.style.cursor = 'not-allowed';
                confirmBtn.disabled = true;
            }
        }

        function checkAndEnter() {
            const check1 = document.getElementById('check1').checked;
            const check2 = document.getElementById('check2').checked;
            const check3 = document.getElementById('check3').checked;
            
            if (check1 && check2 && check3) {
                location.href = '../../admin';
            } else {
                alert('请确认已完成所有配置步骤！');
            }
        }

        // 监听复选框变化
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = ['check1', 'check2', 'check3'];
            checkboxes.forEach(id => {
                const checkbox = document.getElementById(id);
                if (checkbox) {
                    checkbox.addEventListener('change', updateConfirmButton);
                }
            });
        });
    </script>
</body>

</html>