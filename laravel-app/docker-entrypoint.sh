#!/bin/sh
set -e

cd /var/www/html

echo "[entrypoint] waiting for database ..."
until php -r 'try {
    new PDO(
        "pgsql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"),
        getenv("DB_USERNAME"),
        getenv("DB_PASSWORD")
    );
} catch (Exception $e) {
    exit(1);
}'; do
    sleep 1
done

mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chmod -R ug+rwx storage bootstrap/cache || true

if [ "${APP_INIT:-false}" = "true" ]; then
    echo "[entrypoint] running initialization ..."

    if [ ! -d vendor ]; then
        echo "[entrypoint] composer install ..."
        composer install --no-interaction --prefer-dist --no-progress
    fi

    PERF_READ_REPLICA=false php artisan migrate --force
    PERF_READ_REPLICA=false php artisan db:seed --class=AdminUserSeeder --force

    php artisan storage:link >/dev/null 2>&1 || true
else
    echo "[entrypoint] skipping migrations and seeders"
fi

exec "$@"