# source/backend-laravel/Dockerfile
FROM php:8.2-fpm-alpine

# Install dependencies (Sama seperti script manualmu, tapi lebih ringkas)
RUN apk add --no-cache \
    libpng libpq libzip postgresql-libs nginx supervisor curl \
    libpng-dev postgresql-dev libzip-dev zlib-dev freetype-dev libjpeg-turbo-dev \
    oniguruma-dev icu-dev bash git unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql mbstring exif pcntl bcmath gd zip intl \
    && docker-php-ext-enable opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy semua file Laravel
COPY . .

# Install Dependencies Laravel (Production Mode)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permission (Penting agar container user bisa baca/tulis)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
