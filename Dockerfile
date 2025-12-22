# Sử dụng fpm để tối ưu hơn cho server, hoặc giữ cli nếu bạn muốn dùng artisan serve
FROM php:8.2-fpm

# Cài đặt các thư viện hệ thống cần thiết cho các extension PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Cài đặt Composer chính thức từ Docker Hub
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Cấp quyền cho user hiện tại (Để tránh lỗi Permission denied trên Linux)
RUN chown -R www-data:www-data /var/www/html

# Port cho Laravel
EXPOSE 8000

# Lệnh khởi chạy server
# Lưu ý: Trên server thật, nên dùng 'php-fpm', nhưng để khớp với flow hiện tại của bạn:
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]