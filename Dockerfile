FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/* \
    && sed -ri 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf

COPY web/my_website/ /var/www/html/

RUN mkdir -p /var/www/html/db \
    && chown -R www-data:www-data /var/www/html/db \
    && printf 'ServerName localhost\n' >> /etc/apache2/apache2.conf

EXPOSE 10000
