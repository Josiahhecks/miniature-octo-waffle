FROM php:8.2-apache
COPY index.php /var/www/html/index.php
RUN docker-php-ext-install curl
