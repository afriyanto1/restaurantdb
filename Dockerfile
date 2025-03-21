FROM php:8.1-apache
LABEL maintainer="Afri Yanto <afriyanto01002@gmail.com>"

# Install system dependencies and PHP extensions
RUN apt-get update && \
    apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && a2enmod rewrite headers status

# Configure Apache logging and server status
RUN { \
    echo 'ErrorLog /var/log/apache2/error.log'; \
    echo 'CustomLog /var/log/apache2/access.log combined'; \
    echo 'LogLevel warn'; \
    echo '<Location /server-status>'; \
    echo '    SetHandler server-status'; \
    echo '    Require all granted'; \
    echo '</Location>'; \
    echo 'ErrorLogFormat "[%t] [%l] [pid %P] %F: %E: [client %a] %M"'; \
} >> /etc/apache2/sites-available/000-default.conf

# Create directories and set permissions
RUN mkdir -p \
    /var/www/html/logs \
    /var/log/apache2 \
    && chown -R www-data:www-data \
        /var/www/html \
        /var/log/apache2 \
    && chmod -R 775 \
        /var/www/html/logs \
        /var/log/apache2

# Configure document root
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri \
    -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Copy application files
COPY RestaurantProject/ /var/www/html/

# Setup entrypoint for initialization
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Health check configuration
HEALTHCHECK --interval=30s --timeout=30s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]