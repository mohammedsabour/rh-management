# syntax=docker/dockerfile:1

# ============================================================
# 1. Frontend - Vite / Laravel
# ============================================================
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

RUN npm run build


# ============================================================
# 2. PHP base - extensions communes
# ============================================================
FROM php:8.4-cli-bookworm AS php-base

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        libonig-dev \
        libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        mbstring \
        xml \
        intl \
        zip \
        gd \
        bcmath \
        exif \
        pcntl \
        opcache


# ============================================================
# 3. Composer dependencies
# ============================================================
FROM php-base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts


# ============================================================
# 4. Application PHP-FPM
# ============================================================
FROM php:8.4-fpm-bookworm AS app

WORKDIR /var/www/html


# ------------------------------------------------------------
# Dépendances système
# ------------------------------------------------------------
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        libonig-dev \
        libxml2-dev \
    && rm -rf /var/lib/apt/lists/*


# ------------------------------------------------------------
# Extensions PHP
# ------------------------------------------------------------
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        mbstring \
        xml \
        intl \
        zip \
        gd \
        bcmath \
        exif \
        pcntl \
        opcache


# ------------------------------------------------------------
# Configuration PHP production
# ------------------------------------------------------------
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf '%s\n' \
        'memory_limit=512M' \
        'upload_max_filesize=64M' \
        'post_max_size=64M' \
        'max_execution_time=120' \
        'max_input_time=120' \
        'opcache.enable=1' \
        'opcache.memory_consumption=192' \
        'opcache.interned_strings_buffer=16' \
        'opcache.max_accelerated_files=20000' \
        'opcache.validate_timestamps=0' \
        'opcache.revalidate_freq=0' \
        > "$PHP_INI_DIR/conf.d/laravel.ini"


# ------------------------------------------------------------
# Composer
# ------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# ------------------------------------------------------------
# Code
# ------------------------------------------------------------
COPY --chown=www-data:www-data . .


# ------------------------------------------------------------
# Vendor
# ------------------------------------------------------------
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor


# ------------------------------------------------------------
# Vite build
# ------------------------------------------------------------
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build


# ------------------------------------------------------------
# Filament assets
# ------------------------------------------------------------
RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
    php artisan filament:upgrade


# ------------------------------------------------------------
# Laravel directories
# ------------------------------------------------------------
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache


EXPOSE 9000

CMD ["php-fpm"]


# ============================================================
# 5. Nginx
# ============================================================
FROM nginx:1.27-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy the complete Laravel public directory from the app stage.
# This includes Vite + Filament assets.
COPY --from=app /var/www/html/public /var/www/html/public

RUN rm -rf /var/www/html/public/storage \
    && ln -s /var/www/html/storage/app/public /var/www/html/public/storage


EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]