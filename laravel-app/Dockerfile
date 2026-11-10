FROM php:8.3-cli

WORKDIR /var/www/html

# 1. تحديث وتثبيت حزم النظام الأساسية
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    ca-certificates \
    libpq-dev \
    libzip-dev

# 2. تثبيت الحزم التابعة لـ PHP
RUN apt-get install -y $PHPIZE_DEPS

# 3. تثبيت وتفعيل امتدادات قاعدة البيانات و opcache
RUN docker-php-ext-install pdo pdo_pgsql zip opcache

# 4. تثبيت امتدادات gRPC و Protobuf 
RUN pecl install grpc protobuf \
    && docker-php-ext-enable grpc protobuf \
    && pecl clear-cache \
    && rm -rf /tmp/pear

# 5. تنظيف مخلفات التثبيت لتصغير حجم الصورة
RUN rm -rf /var/lib/apt/lists/*

# 6. تثبيت أداة grpcurl لفحص اتصالات الـ gRPC
RUN curl -sSL https://github.com/fullstorydev/grpcurl/releases/download/v1.9.1/grpcurl_1.9.1_linux_x86_64.tar.gz \
    | tar -xz -C /usr/local/bin grpcurl \
    && chmod +x /usr/local/bin/grpcurl

# 7. إعدادات الـ Opcache للتحسين
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'opcache.revalidate_freq=2'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# 8. جلب مدير الحزم Composer من الصورة الرسمية
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]