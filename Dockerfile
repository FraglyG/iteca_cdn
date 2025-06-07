FROM php:8.2-apache

RUN a2enmod rewrite
RUN docker-php-ext-install fileinfo

WORKDIR /var/www/html

COPY . .

RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html/data && \
    chmod -R 755 /var/www/html/data
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 644 /var/www/html && \
    chmod -R 755 /var/www/html/data

EXPOSE 80
CMD ["apache2-foreground"]