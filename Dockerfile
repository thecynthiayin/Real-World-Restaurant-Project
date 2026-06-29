FROM php:8.2-cli

# Install pdo_mysql extension
RUN apt-get update \
    && apt-get install -y default-libmysqlclient-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && rm -rf /var/lib/apt/lists/*

# Copy project files
COPY . /var/www/html/

WORKDIR /var/www/html

# Railway injects $PORT at runtime, use a shell entrypoint to read it
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
