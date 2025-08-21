FROM php:8.1-fpm

# Устанавливаем зависимости
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath gd

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем только файлы composer и устанавливаем зависимости
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Копируем весь проект
COPY . .

CMD ["php-fpm"]
