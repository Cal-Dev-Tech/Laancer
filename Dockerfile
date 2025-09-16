FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip xml intl opcache

# Opcache recommended settings
RUN echo "opcache.memory_consumption=128\n" \
    "opcache.interned_strings_buffer=8\n" \
    "opcache.max_accelerated_files=4000\n" \
    "opcache.revalidate_freq=2\n" \
    "opcache.validate_timestamps=1\n" \
    > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache modules
RUN a2enmod rewrite ssl

# Ensure AllowOverride All for Laravel .htaccess
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

# Create self-signed SSL certificate for localhost with SAN
RUN mkdir -p /etc/ssl/certs /etc/ssl/private && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout /etc/ssl/private/localhost.key \
        -out /etc/ssl/certs/localhost.crt \
        -subj '/CN=localhost' \
        -addext 'subjectAltName=DNS:localhost,IP:127.0.0.1' && \
    chmod 600 /etc/ssl/private/localhost.key && \
    chmod 644 /etc/ssl/certs/localhost.crt

# Configure SSL virtual host for localhost
RUN echo '<IfModule mod_ssl.c>\n\
    <VirtualHost *:443>\n\
        DocumentRoot /var/www/html\n\
        ServerName localhost\n\
        SSLEngine on\n\
        SSLCertificateFile /etc/ssl/certs/localhost.crt\n\
        SSLCertificateKeyFile /etc/ssl/private/localhost.key\n\
        <Directory /var/www/html>\n\
            Options Indexes FollowSymLinks\n\
            AllowOverride All\n\
            Require all granted\n\
        </Directory>\n\
        ErrorLog ${APACHE_LOG_DIR}/ssl_error.log\n\
        CustomLog ${APACHE_LOG_DIR}/ssl_access.log combined\n\
    </VirtualHost>\n\
</IfModule>' > /etc/apache2/sites-available/default-ssl.conf

# Enable SSL site
RUN a2ensite default-ssl

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copy and configure entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose ports 80 and 443
EXPOSE 80 443

# Use custom entrypoint then start Apache
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]