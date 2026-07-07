FROM php:8.2-apache

# Install PostgreSQL dependency libraries and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite for custom routing rules if needed
RUN a2enmod rewrite

# Copy all source files to the Apache server root
COPY . /var/www/html/

# Create a backup of the initial uploads folder so we can restore them to the volume
RUN cp -R /var/www/html/uploads /var/www/html/uploads_init

# Copy the entrypoint script and make it executable
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set correct ownership and permissions for directory writes (uploads and local databases)
RUN mkdir -p /var/www/html/uploads /var/www/html/data && \
    chown -R www-data:www-data /var/www/html/uploads /var/www/html/data && \
    chmod -R 775 /var/www/html/uploads /var/www/html/data

# Expose the standard Apache web port
EXPOSE 80

# Configure the custom entrypoint script to restore files to the volume on container start
ENTRYPOINT ["docker-entrypoint.sh"]
