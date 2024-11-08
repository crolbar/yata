#!/bin/bash
chown -R www-data:www-data /var/www/html

exec supervisord -c /etc/supervisord.conf
