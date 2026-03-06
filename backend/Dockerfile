FROM php:8.2-cli

# Install system dependencies + PostgreSQL client (pg_isready) + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git curl unzip libpq-dev postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first (leverages Docker layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

# Copy all source code
COPY . .

# Finalise autoloader + run post-install scripts
RUN composer dump-autoload --optimize

# Ensure storage directories are writable
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
