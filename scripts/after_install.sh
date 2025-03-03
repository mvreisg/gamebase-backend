#!/bin/bash
sudo docker compose up
sudo docker exec -it gamebase-backend vendor/bin/phinx migrate