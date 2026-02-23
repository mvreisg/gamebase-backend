#!/bin/bash
php -f ./config/pdo_create_database.php
composer phinx migrate
composer phinx seed:run