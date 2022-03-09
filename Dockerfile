FROM php:7.4.9-apache

ENV PROJECT_ROOT="/var/www/html"

RUN echo "deb http://ftp.debian.org/debian stable main" >> /etc/apt/sources.list.d/apache24.list
RUN apt-get -qq update --fix-missing
RUN apt-get install -y apache2

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN cd /usr/local/etc/php/conf.d/ && echo 'memory_limit = -1' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# Install Postgres PDO for use in php
RUN apt-get -qq install -y libpq-dev zlib1g-dev libpng-dev libzip-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip gd exif

# Install xdebug (locally and in CI) for codeception code coverage reports
RUN if [ $WITH_XDEBUG = "true" ]; then \
        pecl install xdebug; \
        docker-php-ext-enable xdebug; \
        echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
        echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
        echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
        echo "xdebug.mode=develop,debug,coverage" >> "$PHP_INI_DIR/php.ini"; \
    fi;

RUN which git || ( apt-get -qq install git -y )
RUN which wget || ( apt-get -qq install wget -y )
RUN which zip || ( apt-get -qq install zip -y )
RUN wget https://composer.github.io/installer.sig -O - -q | tr -d '\n' > installer.sig \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php'); unlink('installer.sig');"

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite
RUN apachectl start

COPY ./ /var/www/html/

RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap/cache

RUN cd /var/www/html && composer install

# Install node and npm
RUN rm -rf /var/lib/apt/lists/*
RUN apt-get update
RUN apt-get -y install curl gnupg
RUN curl -sL https://deb.nodesource.com/setup_14.x  | bash -
RUN apt-get -y install nodejs
RUN npm install
RUN npm run production

RUN chmod a+w storage/logs
RUN chmod a+w storage/framework/views

RUN cd ${PROJECT_ROOT} \
    && chgrp -R www-data ${PROJECT_ROOT}/storage \
    && chmod -R 775 ${PROJECT_ROOT}/storage \
    && chmod 775 ${PROJECT_ROOT}/db-users.sh

EXPOSE 80
