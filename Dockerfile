# syntax=docker/dockerfile:1

# ============================================================
# 1. Frontend - Vite / Laravel
# ============================================================
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

# Installer les dépendances npm d'abord pour profiter du cache Docker
COPY package.json package-lock.json ./

RUN npm ci

# Copier le projet
COPY . .

# Build Vite
RUN npm run build


# ============================================================
# 2. Backend dependencies - Composer
# ============================================================
FROM composer:2 AS vendor

WORKDIR /app

# Copier uniquement les fichiers Composer
COPY composer.json composer.lock ./

# Installer uniquement les dépendances de production
# --no-scripts : les scripts Laravel seront exécutés après
# que l'environnement de production soit disponible.
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts


# ============================================================
# 3. Application PHP-FPM
# ============================================================
FROM php:8.4-fpm-bookworm AS app

WORKDIR /var/www/html

# ------------------------------------------------------------
# Dépendances système
# ------------------------------------------------------------
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

# ------------------------------------------------------------
# Extensions PHP
# Laravel 13 + Filament 4 + PostgreSQL
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
    && { \
        echo "memory_limit=512M"; \
        echo "upload_max_filesize=64M"; \
        echo "post_max_size=64M"; \
        echo "max_execution_time=120"; \
        echo "max_input_time=120"; \
        echo "opcache.enable=1"; \
        echo "opcache.memory_consumption=192"; \
        echo "opcache.interned_strings_buffer=16"; \
        echo "opcache.max_accelerated_files=20000"; \
        echo "opcache.validate_timestamps=0"; \
        echo "opcache.revalidate_freq=0"; \
    } > "$PHP_INI_DIR/conf.d/laravel.ini"

# ------------------------------------------------------------
# Composer
# ------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ------------------------------------------------------------
# Code source
# ------------------------------------------------------------
COPY --chown=www-data:www-data . .

# ------------------------------------------------------------
# Vendor depuis l'étape Composer
# ------------------------------------------------------------
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor

# ------------------------------------------------------------
# Assets Vite depuis l'étape frontend
# ------------------------------------------------------------
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# ------------------------------------------------------------
# Répertoires Laravel nécessaires
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
# 4. Nginx
# ============================================================
FROM nginx:1.27-alpine AS nginx

# Configuration Nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Fichiers publics Laravel
COPY public /var/www/html/public

# Assets générés par Vite
COPY --from=frontend /app/public/build /var/www/html/public/build

# Le volume storage sera monté par Docker Compose.
# On crée le lien attendu par Laravel :
# /storage/... -> /var/www/html/storage/app/public/...
RUN rm -rf /var/www/html/public/storage \
    && ln -s /var/www/html/storage/app/public /var/www/html/public/storage

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]