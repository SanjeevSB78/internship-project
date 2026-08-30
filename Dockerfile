FROM php:8.2-apache

# System tools needed to build PHP extensions
RUN apt-get update && apt-get install -y \
    libssl-dev \
    libzip-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# MySQL
RUN docker-php-ext-install mysqli

# Redis
RUN pecl install redis && docker-php-ext-enable redis

# MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Copy project into Apache web root
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

# Copy and register the startup fix script
COPY docker-entrypoint-fix.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint-fix.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint-fix.sh"]
CMD ["apache2-foreground"]