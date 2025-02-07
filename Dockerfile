# Deploy everything
FROM trafex/php-nginx@sha256:6d56d20a4752470beff6841f96293e2923ae2cb82617d1c449f6d6ef32f1c234

USER root
RUN apk update
RUN apk add --no-cache php82-pdo php82-pdo_mysql git composer

COPY nginx/default.conf /etc/nginx/conf.d/default.conf

USER nobody

RUN rm -rf /var/www/html/*
RUN mkdir -p /var/www/html/public

WORKDIR /var/www/html

RUN composer require sendgrid/sendgrid

COPY ./ebooks ./ebooks
COPY ./backend ./public