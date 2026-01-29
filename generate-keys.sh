#!/bin/bash

# 生成 FastMovieAI 需要的随机密钥

echo "=========================================="
echo "  FastMovieAI 密钥生成工具"
echo "=========================================="
echo ""

# 生成 PUSH_KEY
PUSH_KEY=$(openssl rand -hex 16)
echo "PUSH_KEY: $PUSH_KEY"

# 生成 PUSH_SECRET
PUSH_SECRET=$(openssl rand -hex 16)
echo "PUSH_SECRET: $PUSH_SECRET"

# 生成 JWT Secret (可选)
JWT_SECRET=$(openssl rand -hex 32)
echo "JWT_SECRET: $JWT_SECRET"

# 生成数据库密码 (可选)
DB_PASSWORD=$(openssl rand -base64 16 | tr -d "=+/" | cut -c1-16)
echo "DB_PASSWORD: $DB_PASSWORD"

# 生成 Redis 密码 (可选)
REDIS_PASSWORD=$(openssl rand -base64 16 | tr -d "=+/" | cut -c1-16)
echo "REDIS_PASSWORD: $REDIS_PASSWORD"

echo ""
echo "=========================================="
echo "  已生成所有密钥，请复制到 .env 文件中"
echo "=========================================="
