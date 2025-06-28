# Imagen base con PHP, Apache y extensiones necesarias
FROM php:8.3-apache

# Instala extensiones del sistema y extensiones PHP requeridas
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

# Copia el proyecto al contenedor
COPY . /var/www/html

# Configura DocumentRoot de Apache para apuntar a /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Crea carpeta var y asigna permisos
RUN mkdir -p var && chown -R www-data:www-data var && chmod -R 755 var

# Instala dependencias de Composer sin entorno de desarrollo
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Asegura que el autoloader exista (evita fallo de Symfony en producción)
RUN php -r "file_exists('vendor/autoload_runtime.php') ?: exit(1);"

# Expone el puerto 80 (Render lo detecta automáticamente)
EXPOSE 80

# Comando por defecto para iniciar Apache
CMD ["apache2-foreground"]
