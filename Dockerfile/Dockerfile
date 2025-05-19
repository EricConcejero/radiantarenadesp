# Stage 1: Laravel + PHP + Node.js
FROM php:8.2-fpm AS laravel

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /radiantarena.ericconcejero.me/htdocs/

# Copy all application files first
COPY . .

# Create necessary directories and set permissions
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# Now run Composer after all files are in place
RUN composer install --no-interaction --optimize-autoloader

# Stage 2: Producción con NGINX sin default.conf externo
FROM nginx:latest

# Crear configuración directamente
RUN echo 'server {\n\
    listen 80;\n\
    index index.php index.html;\n\
    root /radiantarena.ericconcejero.me/htdocs/public;\n\
    \n\
    location / {\n\
    try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    \n\
    location ~ \.php$ {\n\
    include fastcgi_params;\n\
    fastcgi_pass 127.0.0.1:9000;\n\
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
    fastcgi_index index.php;\n\
    }\n\
    \n\
    location ~ /\.ht {\n\
    deny all;\n\
    }\n\
    }' > /etc/nginx/conf.d/default.conf

COPY --from=laravel /radiantarena.ericconcejero.me/htdocs/public /radiantarena.ericconcejero.me/htdocs/public

WORKDIR /radiantarena.ericconcejero.me/htdocs/

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
