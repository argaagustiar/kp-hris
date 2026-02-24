# Gunakan image PHP 8.2 dengan Apache
FROM php:8.2-apache

# 1. Install dependencies sistem yang dibutuhkan
# libpq-dev penting untuk koneksi ke PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev 

# 2. Bersihkan cache apt agar image lebih kecil
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Install ekstensi PHP yang dibutuhkan Laravel
# pdo_pgsql wajib untuk PostgreSQL
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# 4. Aktifkan mod_rewrite Apache (wajib untuk URL Laravel)
RUN a2enmod rewrite

# 5. Konfigurasi Document Root Apache ke folder /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 6. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Set working directory
WORKDIR /var/www/html

# 8. Copy semua file project ke dalam container
COPY . .

# 9. Install dependencies Laravel via Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 10. Ubah permission folder storage dan bootstrap cache agar bisa ditulisi
RUN chown -R www-data:www-data storage bootstrap/cache

# 11. Expose port 80 (Render mengharapkan port ini)
EXPOSE 80