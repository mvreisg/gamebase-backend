#!/bin/bash
cd /var/www/html/gamebase-backend
composer install
sudo systemctl restart apache2
