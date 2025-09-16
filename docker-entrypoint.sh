#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! php -r "
try {
    \$pdo = new PDO('mysql:host=mysql;port=3306', 'root', 'rootpassword123');
    echo 'Connected successfully';
    exit(0);
} catch (PDOException \$e) {
    exit(1);
}
" > /dev/null 2>&1; do
    echo "MySQL not ready yet, waiting..."
    sleep 3
done
echo "MySQL is ready!"

# Change to the core directory where Laravel app is located
cd /var/www/html/core

# Install Composer dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cp .env.example .env 2>/dev/null || echo "No .env.example found, creating basic .env"
    
    # Create basic .env if .env.example doesn't exist
    if [ ! -f ".env" ]; then
        cat > .env << EOL
APP_NAME="Xilancer"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost:9090

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=xilancer_db
DB_USERNAME=xilancer_user
DB_PASSWORD=xilancer_pass123

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="\${APP_NAME}"
VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"
EOL
    fi
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/core/storage
chmod -R 775 /var/www/html/core/bootstrap/cache

# Create storage directories if they don't exist
mkdir -p /var/www/html/core/storage/logs
mkdir -p /var/www/html/core/storage/framework/cache
mkdir -p /var/www/html/core/storage/framework/sessions
mkdir -p /var/www/html/core/storage/framework/views
mkdir -p /var/www/html/core/storage/app/public

# Set storage permissions
chmod -R 775 /var/www/html/core/storage
chown -R www-data:www-data /var/www/html/core/storage

# Skip automatic migrations - let the installer handle this
echo "Checking database..."
echo "Database is ready for installation wizard..."

# Clear caches
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache configuration for production
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
# Skip view cache due to missing component - will be handled by the installer
# php artisan view:cache

echo "Starting Apache..."
exec "$@"
