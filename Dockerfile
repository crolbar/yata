FROM ubuntu:latest

ARG PHP_VERSION=8.3

ENV PHP_PM_MAX_CHILDREN=10 \
    PHP_PM_START_SERVERS=3 \
    PHP_MIN_SPARE_SERVERS=2 \
    PHP_MAX_SPARE_SERVERS=4

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        nginx \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-pgsql \
        supervisor

RUN ln -sf /usr/sbin/php-fpm${PHP_VERSION} /usr/sbin/php-fpm

COPY . /var/www/html

# nginx config
COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY .docker/nginx/sites-available/yata.conf /etc/nginx/sites-available/default
# fpm config
COPY .docker/fpm/ /etc/php/${PHP_VERSION}/fpm/
# supervisor config
COPY .docker/supervisor/supervisord.conf /etc/supervisord.conf

EXPOSE 10000

COPY .docker/start.sh /start.sh
RUN chmod 755 /start.sh

CMD ["/start.sh"]
