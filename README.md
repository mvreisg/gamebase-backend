# Gamebase

A simple PHP backend for game database.

Using PHP 8.2.12

Uses Composer as the project package manager.

## Installation

- Make sure you have PHP 8.2.12 installed.
- Make sure you have a Apache HTTP server installed.
- Make sure you have MariaDB installed.
- Make sure you have Composer installed globally and configured in the PATH environment.
- Clone the project.

<hr/>

### Setup

Add a virtual host to your Apache virtual host configuration files:

```xml
<VirtualHost *:80>
    ServerAdmin webmaster@backend.gamebase.com.br
    DocumentRoot "path/to/your/project/gamebase-backend/public"
    ServerName backend.gamebase.com.br
    ErrorLog "logs/backend.gamebase.com.br-error.log"
    CustomLog "logs/backend.gamebase.com.br-access.log" common
</VirtualHost>
```

Also, make sure you have a `.env` file in the root directory with the values specified in `.env.example`.

<hr/>

### Run

```
composer install
```

To install the dependencies.

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

<hr/>
