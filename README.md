# Gamebase-Backend

A simple RESTful PHP backend for game database.

Using [PHP 8.3.25](https://www.php.net/)

Uses [Composer](https://getcomposer.org/) as the project package manager.

Follows the PSR-4 and PSR-12 patterns from [PHP-FIG](https://www.php-fig.org/psr/).

Uses <s>Sodium</s> [Defuse PHP Encryption](https://github.com/defuse/php-encryption) as encrypter.</s>

Uses [JWT](https://jwt.io/) for authentication and [Redis](https://redis.io/) for cache.

Has [Docker](https://www.docker.com/) support.

## Run with Docker

### Setup

Make sure to create a `.env` file in the root directory with `ENVIRONMENT={your-environment}` and `MACHINE={your-machine}`, and another with the values specified below following the pattern: `.env.{environment}.{machine}`.

For example, the `.env` should be something like this:

```
ENVIRONMENT="development"

MACHINE="local"
```

So the `Dotenv` can recognize which environment and machine you want. In the case above, it will search for `.env.development.local` and this file should have everything that is inside `.env.example`.

<hr/>

### Running with Docker

*Windows:*

```
docker-compose up --build -d
```

*Ubuntu:*

```
docker compose up --build -d
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

## Commands

See `composer.json` on the project root folder.

All the operations follows the PSR-12 patterns by [PHP-FIG](https://www.php-fig.org/psr/).

```
composer lint
```

Makes a linting operation on the code, checking by errors.

```
composer lint:fix
```

Fixes the errors.

```
composer format
```

Makes a formatting operation on the code, checking by errors.

```
composer format:fix
```

Fixes the formatting errors and throw warnings.

```
composer phpunit:test
```

Runs the PHPUnit Test Suite.

```
composer phinx
```

The Phinx executable.