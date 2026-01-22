# Dockerfile
FROM php:8.2-fpm AS php

# 必要な拡張
RUN docker-php-ext-install pdo_mysql

# 実行ユーザーなどはデフォルトのままでOK
WORKDIR /var/www
