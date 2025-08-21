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
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем весь проект внутрь контейнера (лучше монтировать через volume)
COPY . .

RUN composer install --no-dev --optimize-autoloader

CMD ["php-fpm"]
