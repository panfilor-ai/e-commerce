#!/bin/sh
set -e

cd /var/www/html

# Wait for MySQL to accept connections
until php artisan db:show >/dev/null 2>&1; do
    echo "Waiting for database..."
    sleep 2
done

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --force 2>/dev/null || true

exec "$@"
