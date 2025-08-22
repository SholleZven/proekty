FROM php:8.1-fpm

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath gd opcache mbstring
    # curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем Composerdocker
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем только composer-файлы для кэширования слоев
COPY composer.json composer.lock ./

# Устанавливаем зависимости Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist || true

# Копируем весь проект после установки зависимостей
COPY . .

# Опционально: создаём storage и cache директории с правильными правами
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
USER www-data

CMD ["php-fpm"]
