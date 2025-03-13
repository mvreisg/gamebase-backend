#!/bin/bash
cd /var/www/html/
sudo docker compose down
sudo docker compose up --build -d