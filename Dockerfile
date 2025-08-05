ARG IMAGE_VERSION=8.2
FROM php:${IMAGE_VERSION}-fpm-alpine

ENV APP_ENV "prod"

WORKDIR /var/www/html/

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install uploadprogress \
    && docker-php-ext-enable uploadprogress \
    && apk del .build-deps $PHPIZE_DEPS \
    && chmod uga+x /usr/local/bin/install-php-extensions && sync \
    && install-php-extensions amqp \
            bcmath \
            bz2 \
            calendar \
            curl \
            exif \
            fileinfo \
            ftp \
            gd \
            gettext \
            imagick \
            imap \
            intl \
#            ldap \
            mbstring \
            mcrypt \
            memcached \
            opcache \
            openssl \
            sockets \
            pcntl \
            pdo \
            pdo_pgsql \
            redis \
            soap \
            sodium \
            sysvsem \
            sysvshm \
            xmlrpc \
            xsl \
            zip \
    &&  echo -e "\n opcache.enable=1 \n opcache.enable_cli=1 \n opcache.memory_consumption=128 \n opcache.interned_strings_buffer=8 \n opcache.max_accelerated_files=4000 \n opcache.revalidate_freq=60 \n opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
## Setup memory limitations \
    &&  echo -e "\n memory_limit = 512M \n post_max_size=50M \n upload_max_filesize=50M \n " >> /usr/local/etc/php/conf.d/docker-php-limits.ini \
    && cd ~ \
## Install composer
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "copy('https://composer.github.io/installer.sig', 'signature');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === trim(file_get_contents('signature'))) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && cp "/usr/local/etc/php/php.ini-production" "/usr/local/etc/php/php.ini"

## modify www-data user to have id 1000
RUN apk add \
        --no-cache \
        --repository http://dl-3.alpinelinux.org/alpine/edge/community/ --allow-untrusted \
        --virtual .shadow-deps \
        shadow \
    && usermod -u 1000 www-data \
    && groupmod -g 1000 www-data \
    && apk del .shadow-deps

COPY . .

# Install Supervisor
RUN apk add --no-cache supervisor mc

# Setting up Supervisor
COPY ./.docker/prod/php-fpm/supervisord.conf /etc/supervisord.conf

# Setting up CRON jobs
RUN echo "0 5 * * *	php /var/www/html/bin/console app:run-24h-job" >> /etc/crontabs/www-data
RUN echo "*/5 * * * *	php /var/www/html/bin/console app:run-5m-job" >> /etc/crontabs/www-data

USER www-data

# Installing Composer packages
RUN mkdir /var/www/html/var \
    # && chmod 0777 /var/www/html/var \
    # Allocate .env file (to avoid build errors)
    && cp ./.env.dist ./.env

RUN composer install --no-dev --optimize-autoloader;
RUN ./bin/console -vvv cache:warmup

USER root

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
