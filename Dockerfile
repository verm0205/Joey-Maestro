FROM php:apache

RUN apt-get update && apt-get install -y git unzip

RUN docker-php-ext-install pdo_mysql

RUN a2dismod mpm_event || true \
    && a2enmod mpm_prefork rewrite

RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/database \
    && chown -R www-data:www-data /var/www/html/database \
    && chmod -R 775 /var/www/html/database