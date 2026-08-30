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

# Fix MPM conflict LAST -- after every package step that could reset it
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load \
          /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork

# Copy project into Apache web root
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80