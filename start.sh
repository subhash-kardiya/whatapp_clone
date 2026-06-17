#!/usr/bin/env bash

# Run migrations
php artisan migrate --force || true

# Cache configs
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink
php artisan storage:link --force

# Start Reverb WebSocket server in background
php artisan reverb:start --host=0.0.0.0 --port=8080 &

# Start queue worker in background
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &

# Start the web server (must be foreground - Railway expects it)
php artisan serve --host=0.0.0.0 --port=$PORT
