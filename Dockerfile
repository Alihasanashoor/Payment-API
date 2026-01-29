FROM php:8.3-cli

# Update package index and install system-level dependencies:
# - unzip, git, curl: development and utility tools
# - libzip-dev: required for PHP zip extension
# - libonig-dev: required for PHP mbstring extension
# Then install PHP extensions:
# - pdo, pdo_mysql: database abstraction layer and MySQL driver

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \    
    libonig-dev \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose API port
EXPOSE 8000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]