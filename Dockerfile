# ─── Stage 1: Node — build frontend assets ────────────────────────────────────
FROM node:20-alpine AS node-builder

WORKDIR /app
COPY package*.json ./
RUN npm ci --no-audit --prefer-offline
COPY . .
RUN npm run build

# ─── Stage 2: PHP — production image ─────────────────────────────────────────
FROM php:8.2-fpm-bookworm AS php-base

# System dependencies + LibreOffice headless (untuk convert docx→pdf)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libreoffice-writer \
    libreoffice-calc \
    default-mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
        intl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP config untuk production
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Install Composer dependencies
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# Copy aplikasi
COPY . .

# Copy built frontend dari stage 1
COPY --from=node-builder /app/public/build ./public/build

# Setup storage & permissions
RUN mkdir -p \
        storage/app/private/templates \
        storage/app/private/undangan \
        storage/app/private/surat \
        storage/app/private/berkas \
        storage/app/private/scan \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Optimize untuk production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache

EXPOSE 9000

CMD ["php-fpm"]
