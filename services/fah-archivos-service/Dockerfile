FROM php:8.3-fpm-alpine

# Instalar dependencias del sistema y extensiones PHP
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    curl-dev \
    openssl-dev \
    linux-headers \
    nginx \
    supervisor \
    libxml2-dev \
    oniguruma-dev

# Instalar extensiones PHP necesarias para AWS SDK + MinIO
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    xml \
    mbstring \
    curl

# Instalar extensiones adicionales
RUN docker-php-ext-enable opcache

# Copiar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html
COPY backend/ .

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copiar configuraciones
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Crear directorios para logs
RUN mkdir -p /var/log/supervisor /var/log/nginx

# Exponer puerto
EXPOSE 8012

# Comando de inicio con Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]