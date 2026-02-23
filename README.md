# Gamebase-Backend

RESTful backend for managing a game database, built with modern PHP practices and a DDD-inspired structure.

This project is designed as a study and portfolio application focusing on:

- Clean architecture
- Secure authentication and encryption
- Containerized development
- Scalable environment configuration

<hr>

## Features

- REST API
- JWT Authentication
- Redis caching
- Database migrations with Phinx
- Encryption (Sodium / Defuse)
- Docker-first development
- PSR-4 and PSR-12 compliant
- Unit testing with PHPUnit
- Environment variables
- Centralized error logging

<hr>

## Tech Stack

- PHP 8.4
- Nginx
- MariaDB
- Redis
- Docker
- Composer
- PHPUnit
- Phinx
- Dotenv

<hr>

## Project Architecture

The project loosely follows DDD principles:

- **Domain**
- **Application**
- **Infrastructure**
- **Presentation**

<hr>

## Project Status

🚧 Active development.

<hr>

## License

GPL-3.0

<hr>

## Running the project

### 1. Environment Configuration

The project uses layered environment files:

```
.env
.env.{environment}.{machine}
```

Example `.env`:

```
ENVIRONMENT=development
MACHINE=local
```

This requires a matching file:

```
.env.development.local
```

Copy all keys from `.env.example` into your environment file and fill in the values.

<hr>

### 2. Environment Variables
#### Nginx

```
NGINX_PORT="80"
NGINX_EXPOSE_PORT="8081"
```

`NGINX_PORT`: internal container port

`NGINX_EXPOSE_PORT`: host port mapped to container

<hr>

#### API Consumers

```
API_CONSUMERS_ADDRESSES="http://localhost:8082,http://localhost:8083"
API_CONSUMERS_ADDRESSES_SEPARATOR=","
```

<hr>

#### Repository (Database)

```
REPOSITORY_ROOT_USERNAME="backend_root"
REPOSITORY_ROOT_PASSWORD="secret"

REPOSITORY_HOST="database"
REPOSITORY_DATABASE="gamebase"
REPOSITORY_USERNAME="user"
REPOSITORY_PASSWORD="password"
REPOSITORY_PORT="3306"
REPOSITORY_CHARSET="utf8mb4"
REPOSITORY_EXPOSE_PORT="3307"
```

<hr>

#### Encryption

Choose one method:

```
ENCRYPTION_METHOD="sodium"
```

Available:
 - `sodium`
 - `defuse`

<hr>

#### Encryption Keys

You may keep the default values, but generating your own keys is recommended.

Enter the PHP container:

```
docker exec -it gamebase-backend-php bash
```

Generate Defuse key:

```
php config/defuse_key.php
```

Generate Sodium key:

```
php config/sodium_key.php
```

Copy the values into:

```
DEFUSE_PHP_ENCRYPTION_KEY="insert here"
SODIUM_CRYPTO_SECRETBOX_KEY="insert here"
```

⚠️ Keep both values secret (even if unused).

<hr>

#### JWT Secret

```
JWT_SECRET="your-secret-key"
```

<hr>

#### Redis

```
REDIS_SCHEME=redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_EXPOSE_PORT=6380
```

<hr>

### 3. Create the Database

Phinx does **not** create databases automatically.

You can create it manually or run:

```
docker exec -it gamebase-backend-php bash
php config/pdo_create_database.php
```

This creates the database defined in:

```
REPOSITORY_DATABASE
```

<hr>

### 4. Start the Application

```
docker compose --env-file .env.{environment}.{machine} up -d
```

Enter the PHP container:

```
docker exec -it gamebase-backend-php bash
```

Run:

```
startup.sh
```

<hr>

### Composer Commands

All commands are defined in `composer.json`.

Formatting follows PSR-12 from [PHP-FIG](https://www.php-fig.org/).

<hr>

#### Code Style

```
composer lint:fix
```

Fixes issues using php-cs-fixer.

```
composer format
```

Shows PSR-12 violations using phpcs.

```
composer format:fix
```

Fixes PSR-12 issues using phpcbf.

<hr>

#### Tests

```
composer phpunit:test
```

Examples:

```
composer phpunit:test:all
```

<hr>

#### Database (Phinx)

```
composer phinx migrate
composer phinx seed:run
```

<hr>

### Summary

Gamebase Backend is a modern PHP REST API demonstrating:

- DDD structure
- secure authentication
- encryption strategies
- containerized infrastructure
- scalable environment configuration
