# ==============================================================================
# Dockerfile - Vitalis PharmaSys (Sistema de Gestión de Farmacia)
# Entorno: PHP 8.4 + Apache + Composer 2 + Node.js 22 LTS
# ==============================================================================

FROM php:8.4-apache

# Establecer directorio de trabajo en el contenedor
WORKDIR /var/www/html

# Definir variables de entorno para Debian no interactivo
ENV DEBIAN_FRONTEND=noninteractive

# 1. Instalar dependencias del sistema y librerías de desarrollo para extensiones PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    mariadb-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        intl \
        bcmath \
        exif \
        pcntl \
        zip \
        gd \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Instalar Composer oficial (Composer 2)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3. Instalar Node.js 22 LTS y npm para compilación y soporte de Vite
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# 4. Configurar Apache: DocumentRoot en /var/www/html/public y habilitar mod_rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN printf '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>\n' > /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite

# 5. Ajustes recomendados de PHP para Laravel (subida de archivos, memoria y límites)
RUN printf 'upload_max_filesize = 64M\n\
post_max_size = 64M\n\
memory_limit = 512M\n\
max_execution_time = 300\n\
date.timezone = UTC\n' > /usr/local/etc/php/conf.d/custom-laravel.ini

# 6. Copiar el código del proyecto al contenedor
COPY . /var/www/html

# 7. Crear estructura de almacenamiento/caché y configurar permisos para www-data
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Script de entrada para asegurar permisos de almacenamiento en volúmenes montados
RUN printf '#!/bin/sh\n\
set -e\n\
mkdir -p /var/www/html/storage/framework/cache/data \\\n\
         /var/www/html/storage/framework/sessions \\\n\
         /var/www/html/storage/framework/views \\\n\
         /var/www/html/storage/logs \\\n\
         /var/www/html/bootstrap/cache\n\
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true\n\
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true\n\
exec "$@"\n' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Puerto HTTP expuesto
EXPOSE 80

# Definir Entrypoint y comando por defecto
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
