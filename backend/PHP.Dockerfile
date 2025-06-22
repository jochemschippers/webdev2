# PHP.Dockerfile

FROM php:fpm

# Install common PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install necessary system packages for Composer and other tools
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    locales \
    libxml2-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring exif pcntl bcmath \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install xml intl # Install XML and Intl extensions for Dompdf

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Explicitly set PHP FPM error logging settings for a production-like environment
# This ensures errors go to logs, not stdout (which would break JSON responses)
RUN echo "display_errors = Off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "display_startup_errors = Off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "log_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "error_log = /dev/stderr" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/html # Set working directory inside the container for your application files

EXPOSE 9000

CMD ["php-fpm"]
