FROM php:8.2-fpm

# Install system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    nginx \
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
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    -vvv

# Clear Laravel caches
RUN php artisan config:clear \
    && php artisan cache:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && php artisan config:cache

# Copy NGINX config and entrypoint
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose Render port
EXPOSE 8080

# Start NGINX + PHP-FPM
CMD ["entrypoint.sh"]
