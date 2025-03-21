#!/bin/bash
set -e

# Create setup flag file if not exists
if [ ! -f /var/www/html/setup_completed.flag ]; then
    touch /var/www/html/setup_completed.flag
    chown www-data:www-data /var/www/html/setup_completed.flag
    chmod 664 /var/www/html/setup_completed.flag
fi

# Set proper permissions on first run
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
find /var/www/html -type d -exec chmod 775 {} \;
find /var/www/html -type f -exec chmod 664 {} \;

exec "$@"