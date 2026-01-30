# Gamebase-Backend

RESTful backend for managing a game database, built with modern PHP practices and DDD-inspired structure.

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

## Tech Stack

- PHP 8.3
- Nginx
- MariaDB
- Redis
- Docker
- Composer
- PHPUnit
- Phinx

## Project Architecture

The project loosely follows DDD principles:

- Domain
- Application
- Infrastructure
- Presentation

## Project Status

Active development.

## License

GPL-3.0 license.

## Running the project

### Setup

Make sure to create a `.env` file in the root directory with `ENVIRONMENT={environment}` and `MACHINE={machine}`, and another with the values specified below following the pattern: `.env.{environment}.{machine}`.

For example, the `.env` should be something like this:

```
ENVIRONMENT="development"

MACHINE="local"
```

So the `Dotenv` can recognize which environment and machine you want. In the case above, it will search for `.env.development.local` and this file should have everything that is inside `.env.example`.

### Setting `NGINX` environment variables.

`NGINX_PORT` is the Docker internal port and `NGINX_EXPOSE_PORT` is the base machine port. By pinging on the base machine port you ping on the docker internal port.

```
NGINX_PORT="80"
NGINX_EXPOSE_PORT="8081"
```

### Setting `API_CONSUMERS` environment variables.

Put the URLs string in `API_CONSUMERS_ADDRESSES` and use the `API_CONSUMERS_ADDRESSES_SEPARATOR` between them. (Don't worry, if you put the separator the code will return a collection of values).

Example:

```
API_CONSUMERS_ADDRESSES="http://localhost:8082,http://localhost:8083"
API_CONSUMERS_ADDRESSES_SEPARATOR=","
```

### Setting `REPOSITORY` environment variables.

`REPOSITORY_PORT` is the Docker internal port and `REPOSITORY_EXPOSE_PORT` is the base machine port. By pinging on the base machine port you ping on the docker internal port.

```
REPOSITORY_ROOT_USERNAME="your root backend username"
REPOSITORY_ROOT_PASSWORD="your root backend password"
REPOSITORY_HOST="database address"
REPOSITORY_DATABASE="database name"
REPOSITORY_USERNAME="database username"
REPOSITORY_PASSWORD="database password"
REPOSITORY_PORT="internal port"
REPOSITORY_CHARSET="database charset"
REPOSITORY_EXPOSE_PORT="docker expose port"
```

### Setting `DEFUSE_PHP_ENCRYPTION_KEY` environment variable.

Run

```
docker compose --env-file ./.env.{environment}.{machine} up php --build
```

Then access `bash` inside `gamebase-backend-php` container then:

```
cd config
php defuse_key.php
```

And copy the code inside the parenthesis to `DEFUSE_PHP_ENCRYPTION_KEY` variable field on your `.env.{environment}.{machine}` file. **keep it a secret!**

### Setting `SODIUM_CRYPTO_SECRETBOX_KEY` environment variable.

Run

```
docker compose --env-file ./.env.{environment}.{machine} up php --build
```

Then access `bash` inside `gamebase-backend-php` container then:

```
cd config
php sodium_key.php
```

And copy the code inside the parenthesis to `SODIUM_CRYPTO_SECRETBOX_KEY` variable field on your `.env.{environment}.{machine}` file. **keep it a secret!**

### Setting `JWT_SECRET` environment variable.

This value can be a string with any value, **but keep it a secret!**

```
JWT_SECRET="potato"
```

### Setting `REDIS` environment variables.

`REDIS_PORT` is the Docker internal port and `REDIS_EXPOSE_PORT` is the base machine port. By pinging on the base machine port you ping on the docker internal port.

```
REDIS_SCHEME="redis protocol"
REDIS_HOST="redis address"
REDIS_PORT="redis internal port"
REDIS_EXPOSE_PORT="redis expose port"
```

### Creating the `DATABASE` *(if it does not exist!)*.

`Phinx` does not creates databases, so you must create it yourself by running the `create_database_[insert your database here].php` configuration file or by manually run the `CREATE DATABASE gamebase;` SQL query on your Database Management System. A MariaDB implementation of the configuration already exists as an example (located in `./config` folder on the root folder of this project).

### Starting the application

Run

```
docker compose --env-file ./.env.{environment}.{machine} up --build
```

Make sure all the containers were successfully initiated, then access the `gamebase-backend-php` container `bash` and execute:

```
composer phinx migrate
```

to run the database migrations, and:

```
composer phinx seed:run
```

to insert the root user to the database. The root username and password are specified in `REPOSITORY_ROOT_USERNAME` and `REPOSITORY_ROOT_PASSWORD` `.env.{environment}.{machine}` file.

## Commands (to be used with Composer)

All the commands can be edited in the `composer.json` file on the project root folder.

All formatting operations follows the PSR-12 patterns designed by [PHP-FIG](https://www.php-fig.org/psr/).

`composer lint:fix` 
Tries to fix the code errors using *php-cs-fixer*.

`composer format`
Outputs the formatting errors based on PSR-12 pattern using *phpcs*.

`composer format:fix`
Tries to fix the formatting errors based on PSR-12 pattern using *phpcbf*.

`composer phpunit:test`
Runs the *PHPUnit* executable, allowing commands like `phpunit:test:all` to run all the unitary tests.

`composer phinx`
Runs the *Phinx* executable, allowing commands like `phinx migrate` and `phinx seed:run`.
