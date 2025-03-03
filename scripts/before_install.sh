#!/bin/bash
docker compose down
sudo rm -rf /home/ubuntu/gamebase-backend
sudo mkdir /home/ubuntu/gamebase-backend
sudo cp /home/ubuntu/.env /home/ubuntu/gamebase-backend/.env