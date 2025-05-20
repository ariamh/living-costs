FROM php:8.2-fpm

WORKDIR /var/www/html

# Install system dependencies
# libpng-dev, libjpeg-dev, libfreetype-dev for GD library
# libzip-dev for zip extension (added this)
# libonig-dev for mbstring
# libxml2-dev for xml
# supervisor for process management (optional, but good for running queue workers)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    nodejs \
    npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
# pdo_mysql for MySQL database connection
# bcmath for arbitrary precision mathematics
# gd for image processing
# zip for handling zip archives
# mbstring for multi-byte string operations
# exif for image metadata
# pcntl for process control (often used by queue workers)
# Ensure zip is installed after libzip-dev is available
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
# Ensure .dockerignore is properly configured to exclude unnecessary files (like vendor, node_modules, .git)
COPY . .

# Install application dependencies
# --no-interaction: Do not ask any interactive questions
# --optimize-autoloader: Convert PSR-0/4 autoloading to classmap to get a faster autoloader.
# --no-dev: Skip installing packages listed in require-dev
# RUN composer install --no-interaction --optimize-autoloader --no-dev
# For development, you might want to include dev dependencies:
RUN composer install --no-interaction --optimize-autoloader

# Install Node.js dependencies
RUN npm install

# Build frontend assets for production
RUN npm run build

# Generate application key (if not already set in .env)
# It's generally better to run this after the container is up or ensure .env is present
# RUN php artisan key:generate

# Set permissions for storage, bootstrap/cache, and public/build directories
# This is crucial for Laravel to be able to write logs, cache, sessions, etc.
# www-data is the default user for php-fpm.
# Ensure public/build exists if npm run build creates it. If not, remove it from chown/chmod.
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs public/build bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache public/build \
    && chmod -R 775 storage bootstrap/cache public/build

# Expose port 9000 and start php-fpm server
# Port 9000 is the default port for PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]
