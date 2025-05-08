FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update -y && apt-get install -y \
    libicu-dev libmariadb-dev unzip zip zlib1g-dev libpng-dev \
    libjpeg-dev libfreetype6-dev libjpeg62-turbo-dev libzip-dev \
    git curl libcurl4-openssl-dev pkg-config libssl-dev \
    nodejs npm supervisor && \
    apt-get clean

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP extensions
RUN docker-php-ext-install gettext intl pdo_mysql zip bcmath opcache pcntl \
    && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Set working directory
WORKDIR /var/www/html

# Configure Git for safe directory
RUN git config --global --add safe.directory /var/www/html

# Create Laravel storage directory structure
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && chown -R www-data:www-data storage \
    && chmod -R 775 storage

# Copy Laravel project files
COPY . .

# Set correct permissions before composer install
RUN chown -R www-data:www-data /var/www/html

# Install Composer dependencies as www-data user
USER www-data
RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader 

# Generate Laravel key
RUN php artisan key:generate

# Regenerate autoload files
RUN composer dump-autoload -o

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# Switch back to root for supervisor setup
USER root

# Set up supervisor for Laravel Horizon
COPY docker/php/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Ensure proper permissions for all Laravel directories
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html/storage -type f -exec chmod 664 {} \; \
    && find /var/www/html/storage -type d -exec chmod 775 {} \; \
    && find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \; \
    && find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \; \
    && chmod -R ug+rwx storage bootstrap/cache

# Expose port
EXPOSE 9000

# Start supervisor and PHP-FPM
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"] 