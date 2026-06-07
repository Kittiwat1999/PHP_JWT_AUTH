# ใช้ PHP + Apache image
FROM php:8.2-apache

# เปิด mod_rewrite (ถ้าอยากใช้ routing)
# RUN a2enmod rewrite

# ติดตั้ง PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Copy โค้ด backend ไปที่ Apache root
COPY . /var/www/html

# ตั้ง working directory
WORKDIR /var/www/html
