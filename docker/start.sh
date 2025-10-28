#!/bin/bash

# Start PHP-FPM
service php8.2-fpm start

# Start Nginx
nginx

# Wait for services to start
sleep 2

# Run migrations (only if APP_ENV is production and database is configured)
if [ "$APP_ENV" = "production" ]; then
    php artisan migrate --force || true
fi

# Keep container running
tail -f /var/log/nginx/access.log /var/log/nginx/error.log

