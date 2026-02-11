FROM php:8.2-fpm

# ติดตั้ง System Dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# ติดตั้ง PHP Extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ตั้งค่า Work Directory
WORKDIR /var/www

# Copy ไฟล์ทั้งหมด
COPY . .

# ติดตั้ง Library ของ Laravel
RUN composer install --no-interaction --optimize-autoloader --no-dev

# ตั้งค่าสิทธิ์ (Permission)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
