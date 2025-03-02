#!/bin/bash
cd /var/www/html/gamebase-backend
sudo chown -R ubuntu:ubuntu /var/www/html/gamebase-backend
sudo chmod -R 775 /var/www/html/gamebase-backend
sudo chown -R www-data:www-data /var/www/html/gamebase-backend
sudo chmod -R 775 /var/www/html/gamebase-backend
composer install
composer phinx migrate
sudo systemctl restart apache2