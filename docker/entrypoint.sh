#!/bin/sh

# Exit on any error
set -e

echo "PlayPulse Panel - Starting container..."

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan db:ping --quiet; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "Database connection established!"

# Run Laravel optimizations
echo "Optimizing Laravel application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database if needed
if [ "$SEED_DATABASE" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
fi

# Clear and warm up cache
echo "Setting up cache..."
php artisan cache:clear
php artisan config:cache

# Create storage link
php artisan storage:link

# Set proper permissions
echo "Setting file permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create log directories
mkdir -p /var/log/supervisor /var/log/nginx

echo "PlayPulse Panel - Ready to serve requests!"

# Start Supervisor to manage services
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf