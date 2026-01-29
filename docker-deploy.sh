#!/bin/bash

# FastMovieAI Docker 部署脚本

set -e

echo "=========================================="
echo "  FastMovieAI Docker 部署脚本"
echo "=========================================="

# 检查 Docker 是否安装
if ! command -v docker &> /dev/null; then
    echo "错误: Docker 未安装，请先安装 Docker"
    exit 1
fi

# 检查 Docker Compose 是否安装
if ! command -v docker-compose &> /dev/null; then
    echo "错误: Docker Compose 未安装，请先安装 Docker Compose"
    exit 1
fi

# 检查 .env 文件是否存在
if [ ! -f .env ]; then
    echo "提示: .env 文件不存在，正在从 .env.example 创建..."
    cp .env.example .env
    
    # 生成随机密钥
    PUSH_KEY=$(openssl rand -hex 16)
    PUSH_SECRET=$(openssl rand -hex 16)
    
    # 更新 .env 文件
    sed -i "s/^PUSH_KEY=.*/PUSH_KEY=$PUSH_KEY/" .env
    sed -i "s/^PUSH_SECRET=.*/PUSH_SECRET=$PUSH_SECRET/" .env
    
    echo "已创建 .env 文件，请根据需要修改配置"
    echo "生成的密钥:"
    echo "  PUSH_KEY: $PUSH_KEY"
    echo "  PUSH_SECRET: $PUSH_SECRET"
    echo ""
    read -p "按回车键继续..."
fi

# 检查是否需要初始化数据库
if [ ! -d "mysql_data" ] && [ ! "$(docker ps -q -f name=fastmovie-mysql)" ]; then
    echo "提示: 首次启动，将初始化数据库"
fi

# 询问用户操作
echo ""
echo "请选择操作:"
echo "1) 构建并启动所有服务"
echo "2) 启动所有服务"
echo "3) 停止所有服务"
echo "4) 重启所有服务"
echo "5) 查看服务状态"
echo "6) 查看服务日志"
echo "7) 清理并重新构建"
echo "8) 退出"
echo ""
read -p "请输入选项 (1-8): " choice

case $choice in
    1)
        echo "正在构建并启动所有服务..."
        docker-compose build
        docker-compose up -d
        echo "等待服务启动..."
        sleep 5
        docker-compose ps
        echo ""
        echo "服务已启动！"
        echo "前端地址: http://localhost/fastmovie/"
        echo "后端地址: http://localhost/"
        ;;
    2)
        echo "正在启动所有服务..."
        docker-compose up -d
        docker-compose ps
        ;;
    3)
        echo "正在停止所有服务..."
        docker-compose down
        echo "服务已停止"
        ;;
    4)
        echo "正在重启所有服务..."
        docker-compose restart
        docker-compose ps
        ;;
    5)
        echo "服务状态:"
        docker-compose ps
        ;;
    6)
        echo "查看服务日志 (按 Ctrl+C 退出):"
        docker-compose logs -f
        ;;
    7)
        echo "正在清理并重新构建..."
        docker-compose down -v
        docker-compose build --no-cache
        docker-compose up -d
        echo "等待服务启动..."
        sleep 5
        docker-compose ps
        echo ""
        echo "服务已重新构建并启动！"
        ;;
    8)
        echo "退出"
        exit 0
        ;;
    *)
        echo "无效选项"
        exit 1
        ;;
esac

echo ""
echo "=========================================="
echo "  常用命令:"
echo "=========================================="
echo "查看日志: docker-compose logs -f"
echo "查看状态: docker-compose ps"
echo "进入后端: docker-compose exec backend sh"
echo "进入 MySQL: docker-compose exec mysql bash"
echo "停止服务: docker-compose down"
echo "=========================================="
