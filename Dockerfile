# edfest_backend/Dockerfile
FROM php:8.2-fpm

# ติดตั้ง System Dependencies และ PHP Extensions ที่จำเป็น
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# เคลียร์ Cache เพื่อลดขนาด Image
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# ติดตั้ง PHP Extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# ติดตั้ง Composer (ตัวจัดการ package ของ PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ตั้งค่า Working Directory
WORKDIR /var/www

# ก๊อปปี้ไฟล์โปรเจกต์ทั้งหมดเข้าไปใน Container
COPY . .

# ติดตั้ง Dependencies ของ Laravel
RUN composer install --optimize-autoloader --no-dev

# ตั้งค่า Permission ให้ Storage เขียนไฟล์ได้
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

CMD ["php-fpm"]