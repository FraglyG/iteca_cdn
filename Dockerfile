FROM php:8.2-apache

RUN a2enmod rewrite
RUN docker-php-ext-install fileinfo

WORKDIR /var/www/html

COPY . .

RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 644 /var/www/html && \
    chmod 755 /var/www/html && \
    chmod 644 /var/www/html/.htaccess && \
    chmod 644 /var/www/html/*.php && \
    chmod -R 777 /var/www/html/data

RUN echo '#!/bin/bash\nchown -R www-data:www-data /var/www/html/data\nchmod -R 777 /var/www/html/data\nexec apache2-foreground' > /start.sh && \
    chmod +x /start.sh

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["/start.sh"]