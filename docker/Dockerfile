FROM php:5.6-apache

# Install system dependencies for MySQL and PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev libmysqli mysql-client postgresql-client && docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Install Xdebug (You might need to adjust version numbers)
RUN pecl install xdebug-2.5.0 && docker-php-ext-enable xdebug

# Add your PHP and Apache configuration here
# COPY your-php.ini /usr/local/etc/php/
# COPY your-apache-config.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80 for Apache
EXPOSE 80
