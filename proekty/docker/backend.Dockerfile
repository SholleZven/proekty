# FROM php:8.1-fpm

# # Устанавливаем системные зависимости
# RUN apt-get update && apt-get install -y \
#     libpq-dev \
#     libzip-dev \
#     unzip \
#     git \
#     curl \
#     libpng-dev \
#     libonig-dev \
#     libxml2-dev \
#     zip \
#     && docker-php-ext-install pdo pdo_pgsql zip bcmath gd opcache mbstring
#     # curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# # Устанавливаем Composerdocker
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# WORKDIR /var/www/html

# # Копируем только composer-файлы для кэширования слоев
# COPY composer.json composer.lock ./

# # Устанавливаем зависимости Laravel
# RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist || true

# # Копируем весь проект после установки зависимостей
# COPY . .

# # Опционально: создаём storage и cache директории с правильными правами
# RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/vendor \
#     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/vendor
# USER www-data

# CMD ["php-fpm"]


# ---------- Stage 1: PHP + Composer ----------

# FROM php:8.1-fpm AS backend

# # Установим зависимости PHP
# RUN apt-get update && apt-get install -y \
#     git unzip libpq-dev libzip-dev libpng-dev curl bash gnupg \
#     && docker-php-ext-install pdo pdo_pgsql zip gd

# WORKDIR /var/www/html

# # Установим Composer
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# # Скопируем только composer.* и установим зависимости PHP
# COPY composer.json composer.lock ./
# RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist || true

# # ---------- Stage 2: Node (Vite build) ----------
# FROM node:20 AS frontend

# WORKDIR /app

# # Скопируем только package.json и vite.config.js для кеша
# COPY package*.json vite.config.js ./

# # Установим зависимости
# RUN npm ci

# # Скопируем исходники Vue и стили
# COPY resources/ ./resources/

# # Запускаем сборку
# RUN npm run build

# # ---------- Stage 3: Final image ----------
# FROM backend AS final

# WORKDIR /var/www/html

# # Скопируем всё Laravel-приложение
# COPY . .

# # Скопируем билд Vue в public/build
# COPY --from=frontend /app/public/build ./public/build

# # Права для storage и cache
# RUN chown -R www-data:www-data storage bootstrap/cache public/build vendor \
#     && chmod -R 777 storage bootstrap/cache public/build vendor

# CMD ["php-fpm"]


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
    && docker-php-ext-install pdo pdo_pgsql zip bcmath gd opcache mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
RUN pecl install redis \
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

# Собираем Vue в build
RUN npm run build

# ---------- Stage 3: Final image ----------
FROM backend AS final

WORKDIR /var/www/html

# Копируем Laravel-приложение
COPY . .

# Копируем билд Vue в public/build
COPY --from=frontend /app/public/build ./public/build

# Права для storage, cache, vendor и build
RUN chown -R www-data:www-data storage bootstrap/cache public/build vendor \
    && chmod -R 777 storage bootstrap/cache public/build vendor

CMD ["php-fpm"]
