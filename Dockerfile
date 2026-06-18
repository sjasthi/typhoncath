FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

# Point Apache at the public directory
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

EXPOSE 80
