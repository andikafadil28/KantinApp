FROM php:8.3-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        dom \
        gd \
        mbstring \
        mysqli \
        simplexml \
        xml \
        xmlreader \
        xmlwriter \
        zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/kantinsakina
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

COPY . ./
COPY docker/apache-kantinsakina.conf /etc/apache2/conf-available/kantinsakina.conf
COPY docker/php-production.ini /usr/local/etc/php/conf.d/production.ini

RUN a2enconf kantinsakina \
    && rm -rf docker \
    && chown -R www-data:www-data assets/img

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl --fail --silent --show-error http://127.0.0.1/kantinsakina/health.php > /dev/null || exit 1
