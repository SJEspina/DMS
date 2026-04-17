# ---------- Frontend build ----------
FROM node:22 AS frontend

WORKDIR /app
COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build


# ---------- PHP / Laravel app ----------
FROM php:8.2-cli

WORKDIR /app

# System packages + PostgreSQL support
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app
COPY . /app

# Copy built frontend assets
COPY --from=frontend /app/public/build /app/public/build

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader

# Laravel writable dirs
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD sh -c "php artisan optimize:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"