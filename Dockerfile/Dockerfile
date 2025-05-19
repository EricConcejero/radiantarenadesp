# Imagen base de PHP con Apache
FROM php:8.1-apache

# Etiqueta para identificar el mantenedor
LABEL maintainer="ericconcejero"

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Limpiar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Configurar Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar primero solo los archivos necesarios para composer
COPY composer.json composer.lock ./

# Instalar dependencias de Composer sin scripts (para evitar errores)
RUN composer install --no-scripts --no-autoloader

# Copiar el resto de archivos del proyecto
COPY . .

# Copiar el .env.example a .env si no existe
RUN if [ ! -f ".env" ]; then cp .env.example .env; fi

# Generar clave de aplicación
RUN php artisan key:generate --force

# Completar la instalación de Composer
RUN composer dump-autoload --optimize

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Instalar dependencias de Node.js y compilar assets
RUN npm install && npm run build

# Exponer puerto 80
EXPOSE 80

# Comando de arranque
CMD ["apache2-foreground"]
