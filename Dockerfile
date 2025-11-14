# multi-stage Dockerfile for inventory_saas app (PHP 8.3)
# Stage 1: base PHP-FPM (build runtime)
FROM php:8.3-fpm AS base

WORKDIR /var/www

# install system dependencies required for extensions and tooling
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    ca-certificates \
    gnupg2 \
    zip \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    build-essential \
    procps \
    locales \
    # Add Redis extension dependencies
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath gd zip intl sockets

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install composer (copy from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create non-root user for better file permissions during composer install
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} appuser || true \
 && useradd -u ${UID} -ms /bin/bash -g ${GID} appuser || true

# Copy application code
COPY . /var/www

# Set proper Laravel permissions BEFORE switching to non-root user
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
 && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
 && chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
 && mkdir -p /var/www/storage/framework/views /var/www/storage/framework/sessions /var/www/storage/framework/cache \
 && mkdir -p /var/www/storage/logs /var/www/storage/app/public \
 && touch /var/www/storage/logs/laravel.log \
 && chown -R www-data:www-data /var/www/storage/logs /var/www/storage/framework \
 && chmod -R 775 /var/www/storage/logs /var/www/storage/framework

# Set ownership for the entire application to appuser for composer
RUN chown -R appuser:appuser /var/www

# Switch to non-root user for composer install
USER appuser

# Ensure composer runs without interactive warnings about superuser in CI
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

# Install PHP dependencies (use composer.lock if present)
# This step will be cached if composer.json / composer.lock don't change
RUN if [ -f composer.lock ]; then \
      composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader; \
    else \
      composer install --no-interaction --prefer-dist --optimize-autoloader; \
    fi

# Switch back to root to set final permissions for runtime
USER root

# Final permission setup for Laravel to run properly
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
 && chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
 && chown www-data:www-data /var/www/storage/logs/laravel.log \
 && chmod 664 /var/www/storage/logs/laravel.log

# Ensure the storage and bootstrap directories are writable by www-data
RUN chgrp -R www-data /var/www/storage /var/www/bootstrap/cache \
 && chmod -R ug+rwx /var/www/storage /var/www/bootstrap/cache

# Create a script to handle permissions at runtime (optional but recommended)
RUN echo '#!/bin/bash\n\
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache\n\
chmod -R 775 /var/www/storage /var/www/bootstrap/cache\n\
exec "$@"' > /usr/local/bin/start.sh \
 && chmod +x /usr/local/bin/start.sh

# Expose php-fpm port (internal)
EXPOSE 9000

# Use the custom start script to ensure permissions are set on container start
CMD ["/usr/local/bin/start.sh", "php-fpm"]