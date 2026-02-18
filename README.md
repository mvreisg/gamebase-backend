# Gamebase-Backend

RESTful backend for managing a game database, built with modern PHP practices and a DDD-inspired structure.

Designed as a study and portfolio project focusing on:

- Clean architecture
- Security (JWT + encryption)
- Containerized development
- Scalable environment configuration

## Features

- REST API
- JWT Authentication
- Redis caching
- Database migrations with Phinx
- Encryption using Defuse PHP Encryption
- Docker-first development
- PSR-4 and PSR-12 compliant
- Unit testing with PHPUnit
- Environment variables

## Tech Stack

- PHP 8.3
- Nginx
- MariaDB
- Redis
- Docker
- Composer
- PHPUnit
- Phinx
- Dotenv

## Project Architecture

The project loosely follows DDD principles:

- Domain
- Application
- Infrastructure
- Presentation

## Project Status

Active development.

## License

GPL-3.0

## Running the project

### Setup

Make sure to create a `.env` file in the root directory with `ENVIRONMENT={environment}` and `MACHINE={machine}`, and another file following the pattern `.env.{environment}.{machine}` containing the values specified below.

For example, the `.env` must be like this:

```
ENVIRONMENT="development"

MACHINE="local"
```

This allows `Dotenv` to determine which environment and machine configuration to load. In the example above, it will load `.env.development.local` which should contain all values from `.env.example`.

### Setting `NGINX` environment variables.

`NGINX_PORT` is the internal Docker port and `NGINX_EXPOSE_PORT` is the host port. Requests to the host port are forwarded to the container port.

```
NGINX_PORT="80"
NGINX_EXPOSE_PORT="8081"
```

### Setting `API_CONSUMERS` environment variables.

Add the URL list to `API_CONSUMERS_ADDRESSES` and separate them using `API_CONSUMERS_ADDRESSES_SEPARATOR`.

Example:

```
API_CONSUMERS_ADDRESSES="http://localhost:8082,http://localhost:8083"
API_CONSUMERS_ADDRESSES_SEPARATOR=","
```

### Setting `REPOSITORY` environment variables.

`REPOSITORY_PORT` is the internal Docker port and `REPOSITORY_EXPOSE_PORT` is the host port. Requests to the host port are forwarded to the container port.

```
REPOSITORY_ROOT_USERNAME="your root backend username"
REPOSITORY_ROOT_PASSWORD="your root backend password"
REPOSITORY_HOST="database host"
REPOSITORY_DATABASE="database name"
REPOSITORY_USERNAME="database username"
REPOSITORY_PASSWORD="database password"
REPOSITORY_PORT="internal port"
REPOSITORY_CHARSET="database charset"
REPOSITORY_EXPOSE_PORT="docker expose port"
```

### Setting `ENCRYPTION_METHOD` environment variable.

Available methods:
- `sodium` - Sodium
- `defuse` - PHP Defuse 

*The choice is mandatory.*

### Setting `DEFUSE_PHP_ENCRYPTION_KEY` environment variable.

Run:

```
docker compose --env-file ./.env.{environment}.{machine} up php --build
```

Then access `bash` inside `gamebase-backend-php` container and run:

```
cd config
php defuse_key.php
```

Copy the generated key (inside the parentheses) into the `DEFUSE_PHP_ENCRYPTION_KEY` field in your `.env.{environment}.{machine}` file. **Keep it secret.**

*Provide the value even if you don't use it. This prevents future errors.*

### Setting `SODIUM_CRYPTO_SECRETBOX_KEY` environment variable.

Run:

```
docker compose --env-file ./.env.{environment}.{machine} up php --build
```

Then access `bash` inside `gamebase-backend-php` container and run:

```
cd config
php sodium_key.php
```

Copy the generated key (inside the parentheses) into the `SODIUM_CRYPTO_SECRETBOX_KEY` field in your `.env.{environment}.{machine}` file. **Keep it secret.**

*Provide the value even if you don't use it. This prevents future errors.*

### Setting `JWT_SECRET` environment variable.

This can be any string value, **but keep it secret.**

```
JWT_SECRET="potato"
```

### Setting `REDIS` environment variables.

`REDIS_PORT` is the internal Docker port and `REDIS_EXPOSE_PORT` is the host port. Requests to the host port are forwarded to the container port.

```
REDIS_SCHEME="redis protocol"
REDIS_HOST="redis address"
REDIS_PORT="redis internal port"
REDIS_EXPOSE_PORT="redis expose port"
```

### Creating the `DATABASE` *(if it does not exist!)*.

`Phinx` does not create databases, so you must create it manually by running the `create_database_[insert your database here].php` configuration file or by executing the `CREATE DATABASE gamebase;` SQL query in your database management system. 

A MariaDB implementation is already provided as an example in the `./config` directory.

### Starting the application

Run:

```
docker compose --env-file ./.env.{environment}.{machine} up --build
```

Make sure all containers start successfully, then access the `gamebase-backend-php` container `bash` and run:

```
composer phinx migrate
```

to run database migrations, and:

```
composer phinx seed:run
```

to insert the root user into the database. The root credentials are defined in `REPOSITORY_ROOT_USERNAME` and `REPOSITORY_ROOT_PASSWORD` inside the `.env.{environment}.{machine}` file.

## Commands (via Composer)

All commands can be configured in `composer.json`.

All formatting operations follow the PSR-12 standard defined by [PHP-FIG](https://www.php-fig.org/psr/).

`composer lint:fix` 
Attempts to fix code issues using *php-cs-fixer*.

`composer format`
Displays formatting issues based on PSR-12 using *phpcs*.

`composer format:fix`
Attempts to fix formatting issues based on PSR-12 using *phpcbf*.

`composer phpunit:test`
Runs *PHPUnit*. Allows commands like `phpunit:test:all` to run all the unit tests.

`composer phinx`
Runs *Phinx*. Allows commands like `phinx migrate` and `phinx seed:run`.
