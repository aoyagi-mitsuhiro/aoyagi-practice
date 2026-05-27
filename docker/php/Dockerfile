FROM php:8.5-apache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y unzip
COPY aoyagi.conf /etc/apache2/sites-available/aoyagi.conf
RUN a2ensite aoyagi.conf
RUN a2dissite 000-default.conf

RUN composer require guzzlehttp/guzzle