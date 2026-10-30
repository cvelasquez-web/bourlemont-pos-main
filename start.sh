#!/bin/bash

# Limpiar cachés
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Ejecutar migraciones usando la URL directa de la base de datos
DB_URL="${DIRECT_DATABASE_URL}" php artisan migrate --force

# Crear cachés para producción (opcional pero recomendado)
php artisan config:cache
php artisan route:cache

# Iniciar PHP-FPM y Nginx
php-fpm -D
nginx -g "daemon off;"