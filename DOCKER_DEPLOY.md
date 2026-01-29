# Docker 部署指南

本文档介绍如何使用 Docker 和 Docker Compose 部署 FastMovieAI 项目。

## 目录结构

```
fastmovieai/
├── docker-compose.yml          # Docker Compose 配置文件
├── .env.example                # 环境变量示例
├── nginx.conf                  # Nginx 主配置
├── nginx.d/                    # Nginx 站点配置
│   └── fastmovie.conf
├── fastmovie-admin/            # 后端项目
│   ├── Dockerfile             # 后端 Docker 镜像
│   ├── .env.docker            # 后端 Docker 环境配置
│   └── supervisord.conf        # Supervisor 配置
└── fastmovie-vue/              # 前端项目
    ├── Dockerfile             # 前端 Docker 镜像
    └── nginx.conf             # 前端 Nginx 配置
```

## 前置要求

- Docker 20.10+
- Docker Compose 2.0+
- 至少 4GB 可用内存
- 至少 10GB 可用磁盘空间

## 快速开始

### 1. 克隆项目

```bash
git clone https://gitee.com/yc_open/ai-short-play.git fastmovie-admin
git clone https://gitee.com/yc_open/ai-short-play-vue.git fastmovie-vue
```

### 2. 配置环境变量

复制环境变量示例文件并修改：

```bash
cp .env.example .env
```

编辑 `.env` 文件，修改以下关键配置：

```bash
# 数据库密码
DB_ROOT_PASSWORD=your_root_password
DB_PASSWORD=your_db_password

# Redis 密码
REDIS_PASSWORD=your_redis_password

# 生成 WebSocket 推送密钥（执行以下命令生成）
PUSH_KEY=$(openssl rand -hex 16)
PUSH_SECRET=$(openssl rand -hex 16)
```

生成随机密钥：

```bash
# 生成 PUSH_KEY
openssl rand -hex 16

# 生成 PUSH_SECRET
openssl rand -hex 16
```

将生成的密钥填入 `.env` 文件。

### 3. 构建并启动服务

```bash
# 构建镜像
docker-compose build

# 启动所有服务
docker-compose up -d

# 查看服务状态
docker-compose ps

# 查看日志
docker-compose logs -f
```

### 4. 初始化数据库

首次启动时，数据库会自动初始化。如果需要手动导入：

```bash
# 进入 MySQL 容器
docker-compose exec mysql bash

# 导入数据库
mysql -u root -p fastmovie < /docker-entrypoint-initdb.d/init.sql
```

### 5. 访问系统

- **前端地址**: http://localhost/fastmovie/
- **后端 API**: http://localhost/
- **默认管理员账号**: admin
- **默认密码**: 123456

## 服务说明

### MySQL

- **端口**: 3306
- **数据库**: fastmovie
- **用户**: fastmovie
- **密码**: 配置在 `.env` 中

### Redis

- **端口**: 6379
- **密码**: 配置在 `.env` 中

### Backend (PHP/Webman)

- **端口**: 36300
- **运行模式**: 守护进程模式
- **日志位置**: `/var/log/supervisor/`

### Frontend (Vue3)

- **部署方式**: 静态文件部署
- **访问路径**: `/fastmovie/`

### Nginx

- **HTTP 端口**: 80
- **HTTPS 端口**: 443
- **反向代理**: 将请求转发到后端服务

## 常用命令

### 服务管理

```bash
# 启动所有服务
docker-compose up -d

# 停止所有服务
docker-compose down

# 重启所有服务
docker-compose restart

# 重启指定服务
docker-compose restart backend

# 查看服务状态
docker-compose ps

# 查看服务日志
docker-compose logs -f

# 查看指定服务日志
docker-compose logs -f backend
```

### 进入容器

```bash
# 进入后端容器
docker-compose exec backend sh

# 进入 MySQL 容器
docker-compose exec mysql bash

# 进入 Redis 容器
docker-compose exec redis sh

# 进入 Nginx 容器
docker-compose exec nginx sh
```

### 数据备份与恢复

```bash
# 备份数据库
docker-compose exec mysql mysqldump -u root -p fastmovie > backup.sql

# 恢复数据库
docker-compose exec -T mysql mysql -u root -p fastmovie < backup.sql

# 备份所有数据
docker-compose exec mysql mysqldump -u root -p --all-databases > all_backup.sql
```

### 查看日志

```bash
# 查看后端日志
docker-compose exec backend cat /var/log/supervisor/webman-stdout.log

# 查看 PHP-FPM 日志
docker-compose exec backend cat /var/log/supervisor/php-fpm-stderr.log

# 查看 Nginx 日志
docker-compose exec nginx cat /var/log/nginx/error.log

# 查看 MySQL 慢查询日志
docker-compose exec mysql cat /var/log/mysql/slow.log
```

## 生产环境部署

### 1. 配置 HTTPS

修改 `nginx.d/fastmovie.conf`，添加 SSL 配置：

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;

    # ... 其他配置
}

# HTTP 重定向到 HTTPS
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

将 SSL 证书挂载到容器：

```yaml
nginx:
  volumes:
    - ./ssl/cert.pem:/etc/nginx/ssl/cert.pem:ro
    - ./ssl/key.pem:/etc/nginx/ssl/key.pem:ro
```

### 2. 性能优化

#### 调整 Docker 资源限制

在 `docker-compose.yml` 中添加资源限制：

```yaml
services:
  backend:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          cpus: '1'
          memory: 1G
```

#### 优化 PHP 配置

修改 `fastmovie-admin/Dockerfile`：

```dockerfile
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "pm.max_children=50" > /usr/local/etc/php-fpm.d/zz-custom.conf \
    && echo "pm.start_servers=5" >> /usr/local/etc/php-fpm.d/zz-custom.conf \
    && echo "pm.min_spare_servers=5" >> /usr/local/etc/php-fpm.d/zz-custom.conf \
    && echo "pm.max_spare_servers=35" >> /usr/local/etc/php-fpm.d/zz-custom.conf
```

#### 启用 OPcache

在 `fastmovie-admin/Dockerfile` 中已默认启用 OPcache，可以进一步优化：

```dockerfile
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini
```

### 3. 安全加固

#### 修改默认端口

在 `.env` 中修改端口配置：

```bash
BACKEND_PORT=36300
PUSH_API_PORT=36301
PUSH_WSS_PORT=36302
NGINX_HTTP_PORT=8080
NGINX_HTTPS_PORT=8443
```

#### 配置防火墙

```bash
# 只允许访问必要端口
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

#### 定期更新镜像

```bash
# 拉取最新镜像
docker-compose pull

# 重新构建并启动
docker-compose up -d --build
```

## 故障排查

### 容器无法启动

```bash
# 查看详细日志
docker-compose logs backend

# 检查端口占用
netstat -tuln | grep -E '3306|6379|36300|80'

# 检查磁盘空间
df -h
```

### 数据库连接失败

```bash
# 检查 MySQL 服务状态
docker-compose ps mysql

# 测试数据库连接
docker-compose exec backend php -r "new PDO('mysql:host=mysql;dbname=fastmovie', 'fastmovie', 'password');"
```

### Redis 连接失败

```bash
# 测试 Redis 连接
docker-compose exec redis redis-cli -a your_redis_password ping

# 查看 Redis 日志
docker-compose logs redis
```

### 权限问题

```bash
# 修复文件权限
docker-compose exec backend chown -R www-data:www-data /var/www/html

# 修复 runtime 目录权限
docker-compose exec backend chmod -R 777 /var/www/html/runtime
```

### 前端无法访问后端 API

检查 `nginx.d/fastmovie.conf` 中的代理配置：

```nginx
location / {
    proxy_pass http://fastmovie_backend;
    # ... 其他配置
}
```

确保后端服务正常运行：

```bash
docker-compose ps backend
curl http://localhost:36300
```

## 升级指南

### 1. 备份数据

```bash
# 备份数据库
docker-compose exec mysql mysqldump -u root -p fastmovie > backup.sql

# 备份配置文件
cp .env .env.backup
```

### 2. 拉取最新代码

```bash
cd fastmovie-admin
git pull

cd ../fastmovie-vue
git pull
```

### 3. 重新构建镜像

```bash
docker-compose build

# 或只重建指定服务
docker-compose build backend
```

### 4. 重启服务

```bash
docker-compose up -d
```

### 5. 验证更新

```bash
# 检查服务状态
docker-compose ps

# 查看日志
docker-compose logs -f
```

## 监控与日志

### 查看资源使用情况

```bash
# 查看容器资源使用
docker stats

# 查看磁盘使用
docker system df
```

### 清理无用资源

```bash
# 清理停止的容器
docker container prune

# 清理未使用的镜像
docker image prune -a

# 清理未使用的卷
docker volume prune

# 清理未使用的网络
docker network prune
```

### 日志管理

配置日志轮转，在 `docker-compose.yml` 中添加：

```yaml
services:
  backend:
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"
```

## 开发环境

### 本地开发模式

修改 `docker-compose.yml`，挂载本地代码目录：

```yaml
backend:
  volumes:
    - ./fastmovie-admin:/var/www/html
    - /var/www/html/vendor  # 不挂载 vendor 目录
```

这样可以实时修改代码，无需重新构建镜像。

### 调试模式

修改 `.env` 文件：

```bash
DEBUG=true
```

在容器中查看详细错误信息：

```bash
docker-compose exec backend php -v
docker-compose logs -f backend
```

## 支持与反馈

- 问题反馈: [Gitee Issues](https://gitee.com/yc_open/ai-short-play/issues)
- 邮箱: 416716328@qq.com
- 在线演示: [https://fastmovie.ai](https://fastmovie.ai)

## 附录

### 端口说明

| 服务 | 内部端口 | 外部端口 | 说明 |
|------|---------|---------|------|
| MySQL | 3306 | 3306 | 数据库服务 |
| Redis | 6379 | 6379 | 缓存服务 |
| Backend | 36300 | 36300 | 后端 API 服务 |
| Push API | 36301 | 36301 | WebSocket Push API |
| Push WSS | 36302 | 36302 | WebSocket Push WSS |
| Nginx HTTP | 80 | 80 | HTTP 服务 |
| Nginx HTTPS | 443 | 443 | HTTPS 服务 |

### 目录映射

| 容器路径 | 说明 |
|---------|------|
| /var/www/html | 后端代码目录 |
| /var/lib/mysql | MySQL 数据目录 |
| /data | Redis 数据目录 |
| /usr/share/nginx/html | 前端静态资源 |
| /var/log/supervisor | Supervisor 日志目录 |
| /var/log/nginx | Nginx 日志目录 |

### 默认账号密码

- **管理员账号**: admin
- **管理员密码**: 123456

首次登录后请立即修改密码！
