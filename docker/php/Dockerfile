FROM php:8.2-fpm
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN ln -snf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime \
 && echo Asia/Tokyo > /etc/timezone
