# FROM phpswoole/swoole:4.7-php7.4-alpine
FROM phpswoole/swoole:php8.1-zts-dev

RUN docker-php-ext-install mysqli pdo_mysql