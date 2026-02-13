#!/bin/sh
set -e

# Create sqlite database if it doesn't exist in storage
if [ ! -f /var/www/storage/database.sqlite ]; then
    echo "Creating database.sqlite in storage..."
    touch /var/www/storage/database.sqlite
    chown www-data:www-data /var/www/storage/database.sqlite
fi

# Initialize storage structure if missing (persistence)
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs
chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Cache config/routes/views
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm
