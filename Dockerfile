FROM php:8.2-cli

# Install required dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev zip curl \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set environment variable to allow Composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install Symfony CLI (Fix for "symfony-cmd: not found")
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Set the working directory
WORKDIR /var/www/symfony

# Copy the Symfony project
COPY symfony-api/ .

# Install dependencies
RUN composer require symfony/flex --no-interaction
RUN composer install --no-interaction --optimize-autoloader
RUN composer require --dev phpunit/phpunit --no-interaction

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
