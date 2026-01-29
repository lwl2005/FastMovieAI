FROM php:8.1-fpm-alpine

LABEL maintainer="FastMovieAI Team"

WORKDIR /var/www/html

# 安装系统依赖（包含构建工具）
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    libxml2-dev \
    ffmpeg \
    ffmpeg-dev \
    composer \
    supervisor \
    autoconf \
    g++ \
    make \
    libevent-dev \
    openssl-dev \
    pkgconfig

# 安装 PHP 扩展
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    gd \
    zip \
    opcache \
    bcmath \
    mbstring \
    sockets \
    simplexml \
    xml \
    dom

# 安装 Redis 扩展
RUN pecl install redis \
    && docker-php-ext-enable redis

# 安装 Event 扩展（可选，用于提升性能）
RUN pecl install event \
    && docker-php-ext-enable event

# 配置 PHP
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/upload.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/upload.ini \
    && echo "max_execution_time=300" > /usr/local/etc/php/conf.d/execution.ini \
    && echo "date.timezone=Asia/Shanghai" > /usr/local/etc/php/conf.d/timezone.ini

# 配置 Composer 使用阿里云镜像
RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
    && composer config -g process-timeout 600

# 复制项目文件
COPY . .

# 安装 Composer 依赖
RUN composer install --no-dev --optimize-autoloader

# 设置权限
RUN chmod -R 755 /var/www/html \
    && chown -R www-data:www-data /var/www/html

# 创建必要的目录
RUN mkdir -p /var/www/html/runtime \
    /var/www/html/public/storage

# 配置 Supervisor
RUN mkdir -p /var/log/supervisor

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 36300 36301 36302

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
