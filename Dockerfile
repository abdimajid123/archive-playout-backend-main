# Use official PHP image with CLI and extensions
FROM php:8.3-cli

# Install required PHP extensions and system packages
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# Set working directory
WORKDIR /var/www

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install dependencies (production optimized)
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

# Copy app files
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize

# Clear Laravel caches to ensure DB_HOST and DB_CONNECTION are applied
RUN php artisan config:clear \
    && php artisan cache:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port 8080 for Cloud Run
EXPOSE 8080

# Create startup script
RUN echo '#!/bin/sh\n\
PORT=${PORT:-8080}\n\
echo "Starting server on port $PORT"\n\
exec php -S 0.0.0.0:$PORT -t public\n\
' > /start.sh && chmod +x /start.sh

# Use the startup script
CMD ["/start.sh"]
