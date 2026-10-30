FROM php:8.3-fpm

# Instalar dependencias y Nginx
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev libzip-dev nginx \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP (incluida ZIP)
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar proyecto e instalar dependencias
COPY . /var/www
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Permisos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Configuración interna de Nginx para Render (Puerto 10000)
RUN echo "server { \n\
    listen 10000; \n\
    root /var/www/public; \n\
    index index.php index.html; \n\
    location / { \n\
        try_files \$uri \$uri/ /index.php?\$query_string; \n\
    } \n\
    location ~ \.php$ { \n\
        include snippets/fastcgi-php.conf; \n\
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock; \n\
    } \n\
}" > /etc/nginx/sites-available/default

# Exponer puerto (Render usa el 10000)
EXPOSE 10000

# Iniciar Nginx y PHP-FPM
CMD service nginx start && php-fpm