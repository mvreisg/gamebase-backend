# Gamebase-Backend

A simple RESTful PHP backend for game database.

Using [PHP 8.2.12](https://www.php.net/)

Uses [Composer](https://getcomposer.org/) as the project package manager.

Follows the PSR-4 and PSR-12 patterns from [PHP-FIG](https://www.php-fig.org/psr/).

Uses <s>Sodium</s> [Defuse PHP Encryption](https://github.com/defuse/php-encryption) as the encrypter.</s>

Uses [JWT](https://jwt.io/) and [Redis](https://redis.io/) for sessions.

Uses [Docker](https://www.docker.com/) as the container image creator.

## Run with Docker

### Setup

Make sure to create a `.env` file in the root directory with the values specified in `.env.example`.

<hr/>

### Run 

If you have Docker installed, run

```
docker-compose up --build -d
```

or:

```
docker compose up --build -d
```

It will depends on your Docker version.

Make sure all the containers were successfully initiated, the run:

```
docker exec -it gamebase-backend composer phinx migrate
```

to run the database migrations, and:

```
docker exec -it gamebase-backend composer phinx seed:run
```

to insert the root user to the database. 

## Run locally

### Setup

***On Windows**, You can install PHP, Apache and MariaDB using [XAMPP](https://www.apachefriends.org/)*.

- Make sure you have PHP 8.2.12 installed.
- Make sure you have a Apache HTTP server installed (because of .htaccess file).
- Make sure you have MariaDB installed.
- Make sure you have Redis installed.
- Make sure you have Composer installed globally and configured in the PATH environment.
- Clone the project.
- Make sure to create a `.env` file in the root directory with the values specified in `.env.example`.

<hr/>

### Run

First, run:

```
composer install
```

to install the dependencies, then create a database called `gamebase`:

```sql
CREATE DATABASE gamebase;
```

After, run:

```
composer phinx migrate
```

to make Phinx run the migrations, and:

```
composer phinx seed:run
```

to make Phinx insert the root user on the database.

<hr/>

## Commands

See *composer.json* on the project root folder.

All the operations follows the PSR-12 patterns by [PHP-FIG](https://www.php-fig.org/psr/).

```
composer lint
```

Makes a linting operation on the code, checking by errors.

```
composer lint:fix
```

Makes a linting operation on the code, trying to fix the errors.

```
composer format
```

Makes a text formatting in the source code.

```
composer test
```

Runs the PHPUnit Test Suite.
