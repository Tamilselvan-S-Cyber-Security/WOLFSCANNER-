# Wolf Security scanner ~ Docker image
# Copyright (c) Wolf Security scanner Team Sàrl (https://www.cyberwolf.pro)
# Licensed under GNU AGPL-3.0+

FROM php:8.1-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Install Composer dependencies (no dev for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    || composer install --optimize-autoloader --no-interaction --no-scripts

# Configure .htaccess for Docker (RewriteBase /)
RUN sed -i 's|RewriteBase /cyberwolf/|RewriteBase /|g' .htaccess 2>/dev/null || true

# Set writable permissions for runtime directories
RUN mkdir -p tmp assets/logs assets/dashboard assets/lists assets/rules/custom assets/rules/core config/local \
    && chown -R www-data:www-data tmp assets config \
    && chmod -R 755 tmp assets \
    && chmod -R 750 config

# Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]
