FROM php:8.3-cli

# 1. حزم النظام + امتدادات PHP الأساسية
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        ca-certificates \
        libpq-dev \
        libzip-dev \
        libonig-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        mbstring \
        bcmath \
        opcache \
    && rm -rf /var/lib/apt/lists/*

# 2. امتداد Redis (يحتاج أدوات البناء ثم نحذفها)
RUN apt-get update && apt-get install -y --no-install-recommends $PHPIZE_DEPS \
    && pecl install redis-6.1.0 \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && pecl clear-cache \
    && rm -rf /tmp/pear /var/lib/apt/lists/*

# 3. أداة grpcurl (للتواصل مع خدمة المستودع)
RUN curl -sSL https://github.com/fullstorydev/grpcurl/releases/download/v1.9.1/grpcurl_1.9.1_linux_x86_64.tar.gz \
    | tar -xz -C /usr/local/bin grpcurl \
    && chmod +x /usr/local/bin/grpcurl

# 4. إعدادات Opcache
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'opcache.revalidate_freq=2'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# 5. Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 6. سكربت التشغيل (خارج الـ volume حتى لا يُحجب)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]