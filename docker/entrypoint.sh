#!/bin/bash

# Start PHP-FPM in the background
php-fpm &

# Start NGINX in the foreground
nginx -g "daemon off;"
