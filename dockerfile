FROM webdevops/php-nginx:8.2

WORKDIR /app

COPY . /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    nodejs \
    npm \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev

RUN docker-php-ext-install pdo_mysql || true

RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

RUN chown -R application:application /app

ENV WEB_DOCUMENT_ROOT=/app/public

EXPOSE 8080