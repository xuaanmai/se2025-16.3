FROM php:8.2-cli

## Cài đặt các extension PHP cần thiết cho Laravel
RUN apt-get update && apt-get install -y \ 
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

## Mặc định, project sẽ được mount từ host vào container bằng docker-compose
## nên không cần COPY mã nguồn ở đây cho môi trường dev.

## Lệnh chạy Laravel dev server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]


