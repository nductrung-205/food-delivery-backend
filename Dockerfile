FROM php:8.2-cli

# Cài các extension cần thiết cho Laravel + MySQL + Cloudinary
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
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# ✅ Dọn cache cũ trước khi build để tránh lỗi config cũ (ví dụ SQLite)
RUN php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# ✅ Tạo symbolic link cho storage/public
RUN php artisan storage:link || true

# ✅ Build cache config và route
RUN php artisan config:cache && php artisan route:cache

# ✅ Kiểm tra APP_KEY (nếu thiếu thì tự generate, tránh lỗi 500)
RUN if [ -z "$(grep APP_KEY .env | cut -d '=' -f2)" ]; then php artisan key:generate; fi

# ✅ Cấp quyền truy cập cho storage và bootstrap
RUN chmod -R 775 storage bootstrap/cache

# Mở port cho Laravel
EXPOSE 8000

# ✅ Chạy server
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
