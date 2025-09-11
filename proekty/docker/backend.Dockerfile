# ---------- Stage 1: PHP backend ----------
FROM php:8.1-fpm AS backend

# Системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    bash \
    nodejs \
    npm \
    dos2unix \
    vim \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath gd opcache mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
RUN pecl install redis 6.2.0 \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем Laravel composer-файлы и ставим зависимости
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist || true

# ---------- Stage 2: Node (Vite build) ----------
FROM node:20 AS frontend

WORKDIR /app

# Скопируем package.json и package-lock.json для кеша, а vite.config.js отдельно чтобы не перезаписался
COPY package*.json ./

# Установим зависимости
RUN npm ci

# Копируем исходники Vue и стили
COPY resources/ ./resources/
COPY vite.config.js ./
COPY ./public/images/ ./public/images/

# Собираем Vue в build
RUN npm run build

# ---------- Stage 3: Final image ----------
FROM backend AS final

WORKDIR /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/
RUN dos2unix /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Копируем Laravel-приложение
COPY . .

# Копируем билд Vue в public/build
COPY --from=frontend /app/public/build ./public/build

# Права для storage, cache, vendor и build
RUN chown -R www-data:www-data storage bootstrap/cache public/build vendor \
    && chmod -R 777 storage bootstrap/cache public/build vendor

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php-fpm"]
