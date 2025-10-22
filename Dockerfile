FROM php:8.2-cli

# Cài các extension cần thiết
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip git curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www

# Copy toàn bộ mã nguồn vào container
COPY . .

# Cài composer và dependency
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# ✅ Xóa cache cũ trước khi cache lại
RUN php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# ✅ Chạy migrate để đảm bảo kết nối MySQL đúng
RUN php artisan migrate --force

# ✅ Tạo symbolic link storage/public
RUN php artisan storage:link

# ✅ Tạo cache mới (đảm bảo dùng MySQL)
RUN php artisan config:cache && php artisan route:cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=$PORT
