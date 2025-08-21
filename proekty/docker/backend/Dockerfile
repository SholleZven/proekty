FROM php:8.1-fpm

# Устанавливаем расширения и зависимости
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl && \
    gd \
    mbstring \
    mysqli \
    openssl \
    sodium \
    redis \
    docker-php-ext-install pdo pdo_pgsql zip bcmath \
    # Clear cache
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем рабочую директорию
WORKDIR /var/www

# Копируем только composer.* для кеширования зависимостей
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Копируем всё приложение
COPY backend/ .

# Делаем права на storage
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

CMD ["php-fpm"]
