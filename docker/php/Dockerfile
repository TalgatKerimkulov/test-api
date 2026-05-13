FROM php:8.4-fpm-alpine

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
        bash git icu-dev libzip-dev oniguruma-dev postgresql-dev \
        autoconf g++ make linux-headers \
    && docker-php-ext-install pdo_pgsql pgsql mbstring bcmath intl zip opcache pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make linux-headers

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g ${GID} appuser \
    && adduser -D -u ${UID} -G appuser appuser

WORKDIR /var/www/html

USER appuser

CMD ["php-fpm"]
