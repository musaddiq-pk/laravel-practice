FROM php:8.2-fpm

# Set working directory early
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl git libzip-dev libpng-dev libonig-dev libicu-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl xml \
    && apt-get clean

# Optional: Install Node.js (v18 LTS) + npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Install Composer globally
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Permissions for Laravel storage & bootstrap/cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Prevent “host is not writable” on Mac/Windows
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

