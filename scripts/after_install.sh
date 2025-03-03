#!/bin/bash
sudo docker compose build
sudo docker compose up
sudo docker exec -it gamebase-backend bash
composer phinx migrate