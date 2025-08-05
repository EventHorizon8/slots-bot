FROM php:8.2-fpm-alpine

WORKDIR /var/www/html/

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --no-cache supervisor mc \
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
            ldap \
            mbstring \
            mcrypt \
            memcached \
#            opcache \
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
#    &&  echo -e "\n opcache.enable=1 \n opcache.enable_cli=1 \n opcache.memory_consumption=128 \n opcache.interned_strings_buffer=8 \n opcache.max_accelerated_files=4000 \n opcache.revalidate_freq=60 \n opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
#    &&  echo -e "\n xdebug.remote_enable=1 \n xdebug.remote_host=localhost \n xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    &&  echo -e "\n xhprof.output_dir='/var/tmp/xhprof'" >> /usr/local/etc/php/conf.d/docker-php-ext-xhprof.ini \

## Setup memory limitations \
    &&  echo -e "\n memory_limit = 512M \n post_max_size=300M \n upload_max_filesize=300M \n " >> /usr/local/etc/php/conf.d/docker-php-limits.ini \
    && cd ~ \
## Install composer
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "copy('https://composer.github.io/installer.sig', 'signature');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === trim(file_get_contents('signature'))) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && cp "/usr/local/etc/php/php.ini-production" "/usr/local/etc/php/php.ini"

# Setting up Supervisor
COPY supervisord.conf /etc/supervisord.conf

# Setting up CRON jobs
# RUN echo "0 5 * * *	php /var/www/html/bin/console app:run-24h-job" >> /etc/crontabs/root
# RUN echo "*/5 * * * *	php /var/www/html/bin/console app:run-5m-job" >> /etc/crontabs/root

## Install WP-CLI
#    && curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
#    && chmod +x wp-cli.phar \
#    && mv wp-cli.phar /usr/local/bin/wp \
## Install msmtp - To Send Mails on Production & Development
#    && apk add msmtp
## modify www-data user to have id 1000
#RUN apk add \
#        --no-cache \
#        --repository http://dl-3.alpinelinux.org/alpine/edge/community/ --allow-untrusted \
#        --virtual .shadow-deps \
#        shadow \
#    && usermod -u 1000 www-data \
#    && groupmod -g 1000 www-data \
#    && apk del .shadow-deps

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

