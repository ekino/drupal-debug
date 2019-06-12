ARG PHP_VERSION
FROM php:$PHP_VERSION

ENV TERM xterm

RUN apk add --update --upgrade \
    bash \
    curl \
    libxml2 \
    git \
    vim \
    zip \
    patch \
    jpeg-dev libpng libpng-dev libjpeg-turbo-dev libwebp-dev zlib-dev libzip-dev libxpm-dev freetype-dev

RUN deluser www-data && adduser -D -g 'php user' -h /var/www -s /bin/false www-data \
    && docker-php-ext-configure gd \
        --with-jpeg-dir=/usr/include/ \
        --with-freetype-dir=/usr/include/freetype2 \
    && docker-php-ext-install -j "$(nproc)" \
        gd \
        opcache \
        zip \
    && docker-php-source delete \
    && rm -rf /usr/share/vim/vim74/doc/* /usr/share/vim/vim74/tutor/* /usr/src/php.tar* /var/cache/apk/*

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer \
    && composer global require hirak/prestissimo

RUN { \
        echo 'expose_php = 0'; \
        echo 'post_max_size = 40M'; \
        echo 'upload_max_filesize = 20M'; \
        echo 'max_file_uploads = 10'; \
        echo 'opcache.memory_consumption = 1024'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files = 10007'; \
        echo 'opcache.revalidate_freq=60'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'memory_limit=2048M'; \
        echo 'realpath_cache_size = 64k'; \
        echo 'realpath_cache_ttl = 3600'; \
        echo 'error_reporting = E_ALL | E_STRICT'; \
    } > /usr/local/etc/php/conf.d/php.ini

WORKDIR /app
