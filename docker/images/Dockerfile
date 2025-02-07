FROM php:8.2-fpm

ARG USER_ID
ARG GROUP_ID

# system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip

# PHP extensions
RUN docker-php-ext-install zip pdo pdo_mysql

# composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# system user
RUN groupadd --force -g $GROUP_ID dev
RUN useradd -G www-data,root -u $USER_ID -g $GROUP_ID -d /home/dev dev
RUN mkdir -p /home/dev/.composer && \
    chown -R dev:dev /home/dev

# set directory and permissions
WORKDIR /var/www/html
RUN chown -R dev:dev /var/www/html

# log file
RUN mkdir -p /var/www/html/var/log \
    && touch /var/www/html/var/log/dev.log \
    && chown -R dev:dev /var/www/html/var/log \
    && chmod -R 777 /var/www/html/var/log \
    && chmod 666 /var/www/html/var/log/dev.log

USER dev

CMD ["php-fpm"]