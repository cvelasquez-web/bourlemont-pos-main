#!/bin/bash

echo "Iniciando despliegue..."

# 1. Crear un archivo .env de producción dentro del contenedor
cat > /var/www/.env <<EOL
APP_NAME=Laravel
APP_ENV=production
APP_DEBUG=true
APP_URL=https://bourlemont-pos-main.onrender.com
APP_KEY=base64:ttfkYodoyUY/XD4cp1msFFc161Zk20zpvv5+0FDl7gs=
LOG_CHANNEL=stderr
DB_CONNECTION=pgsql
DB_URL=${DIRECT_DATABASE_URL}
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
EOL

# 2. Dar permisos al archivo .env y a los logs
chown www-data:www-data /var/www/.env
chmod 775 /var/www/.env

# 3. Limpiar TODAS las cachés (incluyendo vistas y rutas)
php artisan optimize:clear

# 4. Ejecutar migraciones
php artisan migrate --force

# 5. Sembrar la base de datos (comentado para evitar duplicados)
# php artisan db:seed --force

# 6. Crear cachés para producción
php artisan config:cache
php artisan route:cache

# 7. Iniciar PHP-FPM y Nginx
php-fpm -D
nginx -g "daemon off;"