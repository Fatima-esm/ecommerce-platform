#!/bin/sh
# يجهّز Laravel تلقائياً عند تشغيل الحاوية: يثبّت الحزم، ينتظر القاعدة، ينفّذ الهجرات ويزرع حساب المسؤول.
set -e
cd /var/www/html

if [ ! -d vendor ]; then
  echo "[entrypoint] composer install ..."
  composer install --no-interaction --prefer-dist --no-progress
fi

echo "[entrypoint] waiting for database ..."
until php -r 'try { new PDO("pgsql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Exception $e) { exit(1); }'; do
  sleep 1
done

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || true

# الهجرات والبذور دائماً على القاعدة الأساسية (وليس على نسخة القراءة)
PERF_READ_REPLICA=false php artisan migrate --force
PERF_READ_REPLICA=false php artisan db:seed --class=AdminUserSeeder --force
php artisan storage:link >/dev/null 2>&1 || true

exec "$@"
