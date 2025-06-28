FROM php:8.3-apache

# Instala extensiones necesarias
RUN apt-get update && apt-get install -y \
    git unzip curl libicu-dev libonig-dev libzip-dev zip libpq-dev \
    && docker-php-ext-install intl pdo pdo_mysql opcache zip

# Habilita mod_rewrite y permite .htaccess
RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Instala Composer manualmente
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia el proyecto
COPY . .

# Configura DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Crea carpeta var y da permisos
RUN mkdir -p var && chown -R www-data:www-data var && chmod -R 755 var

# Instala dependencias con scripts y plugins habilitados
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

# Verifica que el autoloader exista
RUN test -f vendor/autoload_runtime.php

EXPOSE 80
CMD ["apache2-foreground"]
