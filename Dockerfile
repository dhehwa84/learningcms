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

# Set ownership
RUN chown -R appuser:appuser /var/www && chmod -R 755 /var/www

# Switch to non-root user
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

# Switch back to root to set correct permissions for volumes at runtime
USER root

# Ensure storage and bootstrap cache directories exist and are writable
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
 && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose php-fpm port (internal)
EXPOSE 9000

# Use php-fpm as default command
CMD ["php-fpm"]
