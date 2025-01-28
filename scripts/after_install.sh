#!/bin/bash
cd /var/www/html/gamebase-backend
sudo chown -R ubuntu:ubuntu /var/www/html/gamebase-backend
sudo chmod -R 755 /var/www/html/gamebase-backend
composer install
sudo chown -R www-data:www-data /var/www/html/gamebase-backend
sudo chmod -R 755 /var/www/html/gamebase-backend
sudo systemctl restart apache2
