#!/bin/bash
sudo docker compose down
sudo rm -rf /var/www/html
sudo mkdir /var/www/html
sudo cp /home/ubuntu/.env /var/www/html/.env