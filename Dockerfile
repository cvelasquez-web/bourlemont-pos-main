FROM php:8.3-fpm

# Instalar dependencias del sistema (incluyendo Node.js y Nginx)
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev libzip-dev nginx \
    nodejs npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP (incluida ZIP y PostgreSQL)
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar el proyecto
COPY . /var/www

# ==========================================
# COMPILAR VITE (CSS y JS)
# ==========================================
# Instalamos las dependencias de Node y compilamos los assets
RUN npm install && npm run build

# Instalar dependencias de Composer
#RUN composer install --no-interaction --optimize-autoloader --no-dev
RUN npm install && VITE_APP_URL="https://bourlemont-pos-main.onrender.com" npm run build

# Permisos (Aseguramos que storage y bootstrap/cache sean escribibles)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

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
# COPIAR EL SCRIPT DE INICIO
# ==========================================
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Ejecutar el script al arrancar
CMD ["/usr/local/bin/start.sh"]