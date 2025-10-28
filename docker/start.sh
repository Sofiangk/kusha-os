#!/bin/bash
set -e

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in background
nginx -g 'daemon off;' &

# Wait for services to start
sleep 3

# Run migrations (only if database is configured)
php artisan migrate --force || echo "Migration failed or database not ready"

# Keep container running
tail -f /var/log/nginx/access.log /var/log/nginx/error.log

