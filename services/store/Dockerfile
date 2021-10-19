FROM php:7.4-fpm

USER root

RUN mkdir -p /var/lib/apt/lists/partial

RUN apt-get update && apt-get install -y libzip-dev
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
# lumen/laravel packages
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# Set working directory
WORKDIR /var/www/services/store


# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install

# Copy existing application directory contents
COPY . /var/www/services/store

# Copy existing application directory permissions
RUN chown -R www-data:www-data /var/www/services/store
RUN php artisan migrate --force

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

