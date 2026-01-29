# Docker 部署配置

本目录包含 FastMovieAI 项目的完整 Docker 部署配置。

## 文件说明

- `docker-compose.yml` - Docker Compose 编排文件
- `.env.example` - 环境变量配置示例
- `nginx.conf` - Nginx 主配置
- `nginx.d/` - Nginx 站点配置目录
- `docker-deploy.sh` - 一键部署脚本
- `DOCKER_QUICKSTART.md` - 快速开始指南
- `DOCKER_DEPLOY.md` - 完整部署文档

## 快速部署

```bash
# 1. 配置环境变量
cp .env.example .env

# 2. 一键部署
./docker-deploy.sh

# 3. 访问系统
# 前端: http://localhost/fastmovie/
# 后端: http://localhost/
```

## 容器架构

```
┌─────────────────────────────────────────┐
│           Nginx (80/443)                 │
│  反向代理 + 静态文件服务                 │
└─────────────────────────────────────────┘
                    │
        ┌───────────┴───────────┐
        │                       │
┌───────▼────────┐     ┌────────▼─────────┐
│  Frontend      │     │   Backend        │
│  (Vue3 静态)   │     │  (PHP/Webman)    │
│  /fastmovie/   │     │  Port: 36300     │
└────────────────┘     └────────┬─────────┘
                               │
                    ┌──────────┴──────────┐
                    │                     │
            ┌───────▼────────┐  ┌────────▼──────┐
            │  MySQL (3306)   │  │  Redis(6379) │
            │  数据库         │  │  缓存         │
            └─────────────────┘  └───────────────┘
```

## 服务端口

| 服务 | 内部端口 | 外部端口 |
|------|---------|---------|
| Nginx HTTP | 80 | 80 |
| Nginx HTTPS | 443 | 443 |
| Backend | 36300 | 36300 |
| MySQL | 3306 | 3306 |
| Redis | 6379 | 6379 |

## 默认账号

- 管理员: admin / 123456

⚠️ 首次登录后请立即修改密码！

## 相关文档

- 快速开始: [DOCKER_QUICKSTART.md](DOCKER_QUICKSTART.md)
- 完整文档: [DOCKER_DEPLOY.md](DOCKER_DEPLOY.md)
