FROM php:7.4-fpm-alpine AS base

# 安装系统依赖和PHP扩展
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    curl-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        curl \
        gd \
        mbstring \
        xml \
        zip \
        fileinfo \
        bcmath \
        opcache \
    && rm -rf /var/cache/apk/*

# 设置时区 + 安装 cron（定时任务）
RUN apk add --no-cache tzdata dcron \
    && cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime \
    && echo "Asia/Shanghai" > /etc/timezone \
    && apk del tzdata

# 创建应用目录
RUN mkdir -p /app /run/nginx /var/log/supervisor /var/log/php

# 复制 Nginx 配置
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# 复制 PHP 配置
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf

# 复制 Supervisor 配置
COPY docker/supervisord.conf /etc/supervisord.conf

# 复制应用代码
COPY . /app

# 复制数据库配置（从环境变量读取）
COPY docker/config.php /app/config.php

# 设置工作目录
WORKDIR /app

# 安装 cron 定时任务
COPY docker/crontab /etc/crontabs/root

# 确保 PHP-FPM 传递环境变量
RUN echo 'clear_env = no' >> /usr/local/etc/php-fpm.d/www.conf

# 创建必要目录并设置权限
RUN mkdir -p /app/runtime/log /app/runtime/cache /app/uploads /app/logs \
    && chmod -R 755 /app \
    && chown -R nobody:nobody /app/runtime /app/uploads /app/logs

# 注入构建信息
ARG GIT_SHA=unknown
ARG BUILD_TIME=unknown
RUN echo "{\"version\":\"${GIT_SHA}\",\"build_time\":\"${BUILD_TIME}\"}" > /app/version.json

# 健康检查
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
