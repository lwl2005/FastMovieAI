FROM node:20-alpine AS builder

WORKDIR /app

# 设置 npm 使用淘宝镜像
RUN npm config set registry https://registry.npmmirror.com/

# 复制 package.json 和 package-lock.json
COPY package*.json ./

# 安装依赖（包含开发依赖，构建需要）
RUN npm ci

# 复制源代码
COPY . .

# 设置构建参数
ARG VITE_API_URL=http://localhost:36300
ARG VITE_REQUEST_BASE_URL=http://localhost:36300

# 创建 .env.production 文件
RUN echo "VITE_API_URL=${VITE_API_URL}" > .env.production \
    && echo "VITE_REQUEST_BASE_URL=${VITE_REQUEST_BASE_URL}" >> .env.production

# 构建项目
RUN npm run build

# 生产环境镜像
FROM nginx:alpine

# 复制构建产物
COPY --from=builder /app/dist /usr/share/nginx/html

# 复制 nginx 配置
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
