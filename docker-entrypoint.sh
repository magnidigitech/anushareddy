#!/bin/bash
set -e

echo "Starting container entrypoint..."

# Copy default/initial files to the persistent uploads volume if they are missing
if [ -d "/var/www/html/uploads_init" ]; then
    echo "Syncing initial assets to persistent uploads volume..."
    # -n prevents overwriting files that already exist in the destination volume
    cp -rn /var/www/html/uploads_init/* /var/www/html/uploads/ || true
    
    # Reset permissions to ensure Apache can read and write files in the volume
    chown -R www-data:www-data /var/www/html/uploads
    chmod -R 775 /var/www/html/uploads
fi

# Run the standard Apache server process in the foreground
echo "Starting Apache web server..."
exec apache2-foreground
