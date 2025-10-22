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
ENV COMPOSER_MEMORY_LIMIT=-1
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# ✅ KHÔNG chạy artisan ở build phase (vì Render chưa inject ENV)
# ✅ Chỉ tạo quyền truy cập file
RUN chmod -R 775 storage bootstrap/cache

# Mở port cho Laravel
EXPOSE 8000

# ✅ Khi container khởi động (đã có biến ENV của Render) → chạy artisan
CMD php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan key:generate --force && \
    php artisan storage:link && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
