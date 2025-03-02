# Gamebase-Backend

A simple RESTful PHP backend for game database.

Using [PHP 8.2.12](https://www.php.net/)

Uses [Composer](https://getcomposer.org/) as the project package manager.

Follows the PSR-4 and PSR-12 patterns from [PHP-FIG](https://www.php-fig.org/psr/).

Uses [Sodium](https://www.php.net/manual/en/sodium.installation.php) as the secret mantainer.

Uses [JWT](https://jwt.io/) and [Redis](https://redis.io/) for sessions.

Uses [Docker](https://www.docker.com/) as the container image creator.

## Run with Docker

If you have Docker installed, simply run

```
docker-compose up
```

and

```
docker exec -it gamebase-backend vendor/bin/phinx migrate
```

to run the database migrations then listen to **8080**.

<p><b>MariaDB</b> uses port <b>3312</b> instead of <b>3306.</b></p>

### Commands

See *composer.json*.
<br/>

```
composer lint
```

To make a linting operation on the code, checking by errors.
<br/>

```
composer lint:fix
```

To make a linting operation on the code, trying to fix the errors.
<br/>

```
composer format
```

To make a code formatting in the source code.