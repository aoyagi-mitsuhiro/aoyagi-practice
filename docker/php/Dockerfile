FROM php:8.5-apache

RUN docker-php-ext-install pdo pdo_mysql

COPY aoyagi.conf /etc/apache2/sites-available/aoyagi.conf
RUN a2ensite aoyagi.conf
RUN a2dissite 000-default.conf