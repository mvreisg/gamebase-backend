# Gamebase-Backend

A simple RESTful PHP backend for game database.

Using [PHP 8.2.12](https://www.php.net/)

Uses [Composer](https://getcomposer.org/) as the project package manager.

Follows the PSR-4 and PSR-12 patterns from [PHP-FIG](https://www.php-fig.org/psr/).

## Installation

***On Windows**, You can install PHP, Apache and MariaDB using [XAMPP](https://www.apachefriends.org/)*.

- Make sure you have PHP 8.2.12 installed.
- Make sure you have a Apache HTTP server installed.
- Make sure you have MariaDB installed.
- Make sure you have Composer installed globally and configured in the PATH environment.
- Clone the project.

<hr/>

### Setup

Make sure you have a `.env` file in the root directory with the values specified in `.env.example`.

Set a virtual host to `path/to/this/project/gamebase-backend/public` to make it properly work.

<hr/>

### Run

First, run:

```
composer install
```

To install the dependencies.

<br/>

Then run:

```
composer phinx init .
```

To create the Phinx config file in the root folder (`/path/to/your/project/gamebase-backend`).

<br/>

Then create a database on MariaDB called `gamebase`:

```sql
CREATE DATABASE gamebase;
```

<br/>

Also run:

```
composer phinx migrate
```

To make Phinx create the database tables.


After that, just access [localhost](http://localhost) to see the server status.

<hr/>

### Other commands

See *composer.json*.
<br/>

```
composer lint
```

To make a linting operation on the code, checking by errors.
<br/>

```
composer format
```

To make a code formatting in the source code.