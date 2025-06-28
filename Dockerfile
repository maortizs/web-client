# Imagen base con PHP, Apache, y extensiones comunes
FROM php:8.3-apache

# Instala dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libzip-dev zip libpq-dev \
    && docker-php-ext-install intl pdo pdo_mysql opcache zip

# Habilita mod_rewrite de Apache (Symfony lo necesita)
RUN a2enmod rewrite

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia el proyecto al contenedor
COPY . /var/www/html

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Configura DocumentRoot de Apache si usas /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Da permisos a los directorios necesarios
RUN mkdir -p var && chown -R www-data:www-data var && chmod -R 755 var

# Instala dependencias PHP (excluyendo dev en producción)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Configura DocumentRoot de Apache si usas /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Exponer el puerto 80 (Render lo detecta automáticamente)
EXPOSE 80
