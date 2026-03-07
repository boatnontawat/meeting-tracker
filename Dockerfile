# ใช้ PHP 8.2 พร้อม Apache
FROM php:8.2-apache

# ติดตั้งส่วนเสริมที่ Laravel และ MySQL/TiDB ต้องใช้
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip git curl libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# เปิดใช้งาน Apache Rewrite Module (สำหรับ Laravel Routing)
RUN a2enmod rewrite

# ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ตั้งค่า Working Directory
WORKDIR /var/www/html

# คัดลอกไฟล์ทั้งหมดในโปรเจกต์ลงใน Container
COPY . .

# ติดตั้ง Library ของ PHP
RUN composer install --no-dev --optimize-autoloader

# เปลี่ยนสิทธิ์การเข้าถึงโฟลเดอร์ (สำคัญมากสำหรับ Laravel)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 🌟 สร้าง Config ของ Apache ใหม่เพื่อบังคับให้ชี้ไปที่ /public และอนุญาต Routing
RUN { \
    echo '<VirtualHost *:80>'; \
    echo '    DocumentRoot /var/www/html/public'; \
    echo '    <Directory /var/www/html>'; \
    echo '        AllowOverride All'; \
    echo '        Require all granted'; \
    echo '    </Directory>'; \
    echo '</VirtualHost>'; \
} > /etc/apache2/sites-available/000-default.conf

# เปิด Port 80
EXPOSE 80

# คำสั่งเริ่มต้นเมื่อเครื่อง Server ทำงาน
CMD ["apache2-foreground"]