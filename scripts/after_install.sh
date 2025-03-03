#!/bin/bash
cd /home/ubuntu/gamebase-backend
sudo chown -R ubuntu:ubuntu /home/ubuntu/gamebase-backend
sudo chmod -R 775 /home/ubuntu/gamebase-backend
docker compose up
docker exec -it gamebase-backend vendor/bin/phinx migrate