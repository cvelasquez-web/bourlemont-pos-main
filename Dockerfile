FROM php:8.3-fpm

# Instalar dependencias y Nginx
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev libzip-dev nginx \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP (incluida ZIP y PostgreSQL)
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar proyecto
COPY . /var/www

# ==========================================
# CREAR ARCHIVO .ENV PARA PRODUCCIÓN
# ==========================================
RUN touch .env && \
    echo "APP_NAME=Laravel" >> .env && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "APP_URL=https://bourlemont-pos-main.onrender.com" >> .env && \
    echo "APP_KEY=\${APP_KEY}" >> .env && \
    echo "DB_CONNECTION=pgsql" >> .env && \
    echo "DB_URL=\${DIRECT_DATABASE_URL}" >> .env && \
    echo "SESSION_DRIVER=file" >> .env && \
    echo "CACHE_STORE=file" >> .env && \
    echo "QUEUE_CONNECTION=sync" >> .env

# Instalar dependencias de Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Permisos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Configuración interna de Nginx para Render (Puerto 10000 y TCP 9000)
RUN echo "server { \n\
    listen 10000; \n\
    root /var/www/public; \n\
    index index.php index.html; \n\
    location / { \n\
        try_files \$uri \$uri/ /index.php?\$query_string; \n\
    } \n\
    location ~ \.php$ { \n\
        include snippets/fastcgi-php.conf; \n\
        fastcgi_pass 127.0.0.1:9000; \n\
    } \n\
}" > /etc/nginx/sites-available/default

# Exponer puerto
EXPOSE 10000

# ==========================================
# ARRANCAR LA APLICACIÓN CON MIGRACIONES
# ==========================================
CMD php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    DB_URL="${DIRECT_DATABASE_URL}" php artisan migrate --force && \
    php-fpm -D && nginx -g "daemon off;"