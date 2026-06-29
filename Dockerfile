<<<<<<< HEAD
FROM php:8.2-cli

# Install pdo_mysql extension
RUN apt-get update \
    && apt-get install -y default-libmysqlclient-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && rm -rf /var/lib/apt/lists/*
=======
FROM php:8.2-apache

# Disable all MPM modules first, then enable only prefork
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork

# Install PDO MySQL and other required extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite
>>>>>>> 858fd249fe9ed3cd3bc37d7af7b5e2639022ef58

# Copy project files
COPY . /var/www/html/

<<<<<<< HEAD
WORKDIR /var/www/html

# Railway injects $PORT at runtime, use a shell entrypoint to read it
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
=======
# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Apache config to allow .htaccess
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

EXPOSE 80
CMD ["apache2-foreground"]
>>>>>>> 858fd249fe9ed3cd3bc37d7af7b5e2639022ef58
