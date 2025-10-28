#!/bin/bash
set -e

# Wait for database (optional, for readiness)
# Add your database wait logic here if needed

# Run migrations
echo "Running migrations..."
php artisan migrate --force || echo "Migration failed or database not ready, continuing..."

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

