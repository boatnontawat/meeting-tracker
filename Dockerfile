# ใช้ PHP 8.2 พร้อม Apache
FROM php:8.2-apache

# ติดตั้งส่วนเสริมที่ Laravel ต้องใช้
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip git curl libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# เปิดใช้งาน Apache Rewrite Module
RUN a2enmod rewrite

# ตั้งค่า Working Directory
WORKDIR /var/www/html

# ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# คัดลอกโค้ดทั้งหมดลง Container
COPY . .

# ติดตั้ง Library ของ PHP
RUN composer install --no-dev --optimize-autoloader

# ตั้งค่า Apache ให้ชี้ไปที่ /public และเปิด AllowOverride (แก้ปัญหา 404)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# ให้สิทธิ์ Laravel ในการเขียนไฟล์ (ป้องกันหน้าจอขาว/Error)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# เปิด Port 80
EXPOSE 80

# คำสั่งรันเซิร์ฟเวอร์แบบปกติ (ไม่พึ่งไฟล์ Secret แล้ว)
CMD ["apache2-foreground"]