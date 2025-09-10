#!/bin/bash
set -e

cd /var/www/html

# Публикуем конфигурацию JWT (если это необходимо сделать однократно)
# Опционально: можно проверить, существует ли уже файл конфига
# if [ ! -f config/jwt.php ]; then
    composer require tymon/jwt-auth
    echo "Publishing JWT configuration..."
    php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
# fi

# Генерируем JWT_SECRET, если он не задан в .env
if [ -z "$(grep '^JWT_SECRET=' .env)" ]; then
    echo "Generating JWT_SECRET..."
    php artisan jwt:secret --force
fi

# Выполняем миграции (если нужно)
php artisan migrate --seed

# Очищаем кеш конфигурации и роутов
php artisan config:cache
php artisan route:cache

npm run build

# Запускаем главную команду (переданную как аргументы к docker-entrypoint.sh)
exec "$@"
