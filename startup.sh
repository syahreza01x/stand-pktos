#!/bin/bash

# Ensure Laravel storage and cache directories exist with full permissions
mkdir -p /home/site/wwwroot/storage/framework/sessions
mkdir -p /home/site/wwwroot/storage/framework/views
mkdir -p /home/site/wwwroot/storage/framework/cache/data
mkdir -p /home/site/wwwroot/storage/logs
mkdir -p /home/site/wwwroot/bootstrap/cache
chmod -R 777 /home/site/wwwroot/storage
chmod -R 777 /home/site/wwwroot/bootstrap/cache

# Clear cached config so fresh env variables take effect
php /home/site/wwwroot/artisan config:clear
php /home/site/wwwroot/artisan cache:clear

# Copy custom Nginx configuration
cp /home/site/wwwroot/default /etc/nginx/sites-available/default
cp /home/site/wwwroot/default /etc/nginx/sites-enabled/default

# Reload Nginx
service nginx reload
