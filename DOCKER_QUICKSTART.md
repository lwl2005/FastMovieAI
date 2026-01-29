# Docker 快速部署指南

## 一键部署

本项目已配置完整的 Docker 部署方案，支持一键启动所有服务。

### 前置要求

- Docker 20.10+
- Docker Compose 2.0+
- 至少 4GB 内存
- 至少 10GB 磁盘空间

### 部署步骤

#### 1. 配置环境变量

```bash
# 复制环境变量模板
cp .env.example .env

# 编辑配置文件（可选，使用默认配置也可以）
vim .env
```

#### 2. 一键部署

```bash
# 方式一：使用部署脚本（推荐）
./docker-deploy.sh

# 方式二：手动执行
docker-compose up -d
```

#### 3. 访问系统

部署完成后，访问以下地址：

- **前端界面**: http://localhost/fastmovie/
- **后端 API**: http://localhost/
- **默认账号**: admin / 123456

## 快速开始命令

```bash
# 启动所有服务
docker-compose up -d

# 查看服务状态
docker-compose ps

# 查看日志
docker-compose logs -f

# 停止所有服务
docker-compose down

# 重启服务
docker-compose restart
```

## 服务说明

| 服务 | 端口 | 说明 |
|------|------|------|
| Nginx | 80, 443 | Web 服务器和反向代理 |
| Backend | 36300 | PHP 后端 API 服务 |
| MySQL | 3306 | 数据库服务 |
| Redis | 6379 | 缓存服务 |

## 常见问题

### 端口被占用

修改 `.env` 文件中的端口配置：

```bash
BACKEND_PORT=36300
NGINX_HTTP_PORT=8080
```

### 数据库连接失败

检查 MySQL 容器状态：

```bash
docker-compose ps mysql
docker-compose logs mysql
```

### 查看日志

```bash
# 查看所有服务日志
docker-compose logs -f

# 查看指定服务日志
docker-compose logs -f backend
```

## 详细文档

完整的部署文档请参考: [DOCKER_DEPLOY.md](DOCKER_DEPLOY.md)

## 生产环境建议

1. 修改默认密码（数据库、Redis、管理员）
2. 配置 HTTPS 证书
3. 设置防火墙规则
4. 配置日志轮转
5. 定期备份数据

## 技术支持

- 文档: [DOCKER_DEPLOY.md](DOCKER_DEPLOY.md)
- 问题反馈: [Gitee Issues](https://gitee.com/yc_open/ai-short-play/issues)
