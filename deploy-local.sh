#!/bin/bash

# FastMovieAI 本地部署脚本（非 Docker 环境）
# 适用于 Debian/Ubuntu 系统

set -e

echo "=========================================="
echo "  FastMovieAI 本地部署脚本"
echo "=========================================="

# 检查是否为 root 用户
if [ "$EUID" -ne 0 ]; then
    echo "请使用 root 权限运行此脚本"
    echo "sudo ./deploy-local.sh"
    exit 1
fi

# 获取脚本所在目录
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# 颜色定义
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 1. 安装依赖
log_info "步骤 1/7: 安装系统依赖..."
apt-get update
apt-get install -y \
    curl \
    wget \
    git \
    unzip \
    zip \
    supervisor \
    nginx \
    ufw

# 2. 安装 PHP 8.1
log_info "步骤 2/7: 安装 PHP 8.1..."
apt-get install -y \
    php8.1 \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-redis \
    php8.1-curl \
    php8.1-gd \
    php8.1-mbstring \
    php8.1-xml \
    php8.1-zip \
    php8.1-bcmath \
    php8.1-cli \
    php8.1-sockets

# 3. 安装 Composer
log_info "步骤 3/7: 安装 Composer..."
if ! command -v composer &> /dev/null; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
    composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
    log_info "Composer 安装完成"
else
    log_info "Composer 已安装"
fi

# 4. 安装 MySQL
log_info "步骤 4/7: 安装 MySQL..."
if ! command -v mysql &> /dev/null; then
    export DEBIAN_FRONTEND=noninteractive
    apt-get install -y mysql-server
    log_info "MySQL 安装完成"
else
    log_info "MySQL 已安装"
fi

# 5. 安装 Redis
log_info "步骤 5/7: 安装 Redis..."
if ! command -v redis-server &> /dev/null; then
    apt-get install -y redis-server
    log_info "Redis 安装完成"
else
    log_info "Redis 已安装"
fi

# 6. 安装 Node.js (如果未安装)
log_info "步骤 6/7: 检查 Node.js..."
if ! command -v npm &> /dev/null; then
    log_info "安装 Node.js..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
    npm config set registry https://registry.npmmirror.com/
else
    log_info "Node.js 已安装"
fi

# 7. 配置后端
log_info "步骤 7/7: 配置后端服务..."
cd "$SCRIPT_DIR/fastmovie-admin"

# 复制环境配置文件
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        log_info "已创建 .env 文件"
    else
        log_warn ".env.example 文件不存在，跳过环境配置"
    fi
else
    log_info ".env 文件已存在"
fi

# 安装 Composer 依赖
log_info "安装后端 Composer 依赖..."
composer install --no-dev --optimize-autoloader

# 设置权限
chown -R www-data:www-data "$SCRIPT_DIR/fastmovie-admin"
chmod -R 755 "$SCRIPT_DIR/fastmovie-admin"
chmod -R 777 "$SCRIPT_DIR/fastmovie-admin/runtime" 2>/dev/null || true

# 导入数据库（如果指定）
read -p "是否导入数据库? (y/n): " import_db
if [ "$import_db" = "y" ] || [ "$import_db" = "Y" ]; then
    read -p "MySQL root 密码: " mysql_password
    read -p "数据库名称 (默认: fastmovie): " db_name
    db_name=${db_name:-fastmovie}

    mysql -u root -p"$mysql_password" -e "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -u root -p"$mysql_password" "$db_name" < database.sql
    log_info "数据库导入完成"
fi

# 配置 Supervisor
log_info "配置 Supervisor..."
cat > /etc/supervisor/conf.d/fastmovie-backend.conf << EOF
[program:fastmovie-webman]
command=/usr/bin/php ${SCRIPT_DIR}/fastmovie-admin/start.php start
directory=${SCRIPT_DIR}/fastmovie-admin
user=www-data
autostart=true
autorestart=true
priority=10
stdout_logfile=/var/log/supervisor/fastmovie-webman-stdout.log
stderr_logfile=/var/log/supervisor/fastmovie-webman-stderr.log
environment=HOME="${SCRIPT_DIR}/fastmovie-admin",USER="www-data"
EOF

# 更新 Supervisor
supervisorctl reread
supervisorctl update

# 8. 构建前端
log_info "步骤 8: 构建前端..."
cd "$SCRIPT_DIR/fastmovie-vue"

# 安装依赖
npm install

# 构建生产版本
npm run build

# 复制构建产物到后端 public 目录
mkdir -p "$SCRIPT_DIR/fastmovie-admin/public/fastmovie"
cp -r dist/* "$SCRIPT_DIR/fastmovie-admin/public/fastmovie/"

# 9. 配置 Nginx
log_info "步骤 9: 配置 Nginx..."
cat > /etc/nginx/sites-available/fastmovie << 'EOF'
server {
    listen 80;
    server_name localhost;
    root /var/www/fastmovie-admin/public;
    index index.html index.php;
    client_max_body_size 100M;

    # 前端静态资源
    location /fastmovie/ {
        alias /var/www/fastmovie-admin/public/fastmovie/;
        try_files $uri $uri/ /fastmovie/index.html;

        location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
        }
    }

    # WebSocket Push
    location /app/YOUR_PUSH_KEY {
        proxy_pass http://127.0.0.1:36301;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }

    # 后端 API
    location / {
        try_files \$uri \$uri/ @webman;
    }

    location @webman {
        proxy_set_header Host \$http_host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_set_header Connection "";
        proxy_pass http://127.0.0.1:36300;
    }

    # 拒绝访问 .php 文件
    location ~ \.php$ {
        return 404;
    }

    # 安全头
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
EOF

# 启用站点
ln -sf /etc/nginx/sites-available/fastmovie /etc/nginx/sites-enabled/

# 删除默认站点
rm -f /etc/nginx/sites-enabled/default

# 测试 Nginx 配置
nginx -t

# 10. 启动服务
log_info "步骤 10: 启动所有服务..."

# 启动 MySQL
service mysql start

# 启动 Redis
service redis-server start

# 启动 PHP-FPM
service php8.1-fpm start

# 启动 Supervisor (启动 Webman)
service supervisor start

# 重启 Nginx
service nginx restart

# 设置开机自启
update-rc.d mysql defaults
update-rc.d redis-server defaults
update-rc.d php8.1-fpm defaults
update-rc.d supervisor defaults
update-rc.d nginx defaults

# 11. 配置防火墙
log_info "步骤 11: 配置防火墙..."
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

echo ""
echo "=========================================="
echo "  部署完成！"
echo "=========================================="
echo ""
echo "访问地址:"
echo "  前端: http://localhost/fastmovie/"
echo "  后端: http://localhost/"
echo ""
echo "默认账号:"
echo "  用户名: admin"
echo "  密码: 123456"
echo ""
echo "重要提醒:"
echo "  1. 请立即修改 .env 文件中的数据库和 Redis 密码"
echo "  2. 请将 Nginx 配置中的 YOUR_PUSH_KEY 替换为实际值"
echo "  3. 请立即修改默认管理员密码"
echo ""
echo "常用命令:"
echo "  查看后端日志: tail -f /var/log/supervisor/fastmovie-webman-stderr.log"
echo "  重启后端: supervisorctl restart fastmovie-webman"
echo "  重启 Nginx: service nginx restart"
echo ""
