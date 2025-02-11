# Use the official PHP 8.2 FPM image as the base
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zlib1g-dev \
    espeak \
    zip \
    curl \
    unzip \
    git \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    poppler-utils \
    libpng-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Clear cache to reduce image size
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Remove the default HTML directory
RUN rm -rf /var/www/html

# Copy the application source code
COPY . /var/www

# Set permissions for Laravel (ensure www-data can write to required directories)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Use the www-data user for security
USER www-data

# Expose port 9000 (default PHP-FPM port)
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
