#!/bin/bash
# Fix Apache permissions for Laravel application

echo "Fixing permissions for Apache access..."

# Make /home/bryan accessible to www-data (add execute permission for others)
# This allows www-data to traverse into /home/bryan/Symphony
sudo chmod 751 /home/bryan

# Ensure Symphony directory is accessible
sudo chmod 755 /home/bryan/Symphony

# Ensure public directory is accessible
sudo chmod 755 /home/bryan/Symphony/public

# Set proper ownership (optional - you can keep bryan:bryan if permissions are correct)
# sudo chown -R bryan:www-data /home/bryan/Symphony
# sudo chmod -R 775 /home/bryan/Symphony
# sudo chmod -R 755 /home/bryan/Symphony/public

# Ensure storage and bootstrap/cache are writable
sudo chmod -R 775 /home/bryan/Symphony/storage
sudo chmod -R 775 /home/bryan/Symphony/bootstrap/cache

echo "Permissions fixed!"
echo ""
echo "Now test Apache configuration:"
echo "  sudo apache2ctl configtest"
echo "  sudo systemctl reload apache2"

