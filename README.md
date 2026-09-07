# Gamebase-Backend

RESTful backend for managing a game database, built with modern PHP practices and a DDD-inspired structure.

## Features

- REST API
- JWT Authentication
- Redis caching
- Database migrations with Phinx
- Encryption (Sodium / Defuse)
- Docker-first development
- Unit testing with PHPUnit
- Environment variables with Dotenv
- Logging system with Monolog
- OpenAPI Documentation
- Internal Dashboard

## Tech Stack

- PHP 8.4
- Nginx
- Composer
- Slim Framework
- PHP-DI
- PHPUnit
- Monolog
- Phinx
- Docker
- OpenAPI
- Dotenv
- Redis
- MariaDB
- Twig

## Project Status

🚧 **Active development**

## License

GPL-3.0

## Running the project

### 1. Environment Configuration

The project uses layered environment files:

```
.env
.env.{environment}.{machine}
```

Example `.env` to run with `Docker` under `development` environment:

```
.env.development.docker
```

Copy all keys from `.env.example` into your environment file and fill in the values.

### 2. Environment Variables

#### Timezone

Inform it on **IANA Format**, for example:

```
TIME_ZONE="America/Sao_Paulo"
```

#### Nginx

```
NGINX_PORT="80"
NGINX_EXPOSE_PORT="8081"
```

`NGINX_PORT`: internal container port

`NGINX_EXPOSE_PORT`: host port mapped to container

#### API Consumers

```
API_CONSUMERS_ADDRESSES="http://localhost:8082,http://localhost:8083"
API_CONSUMERS_ADDRESSES_SEPARATOR=","
```

The addresses **will be tokenized** based on the **separator character**.

#### Repository (Database)

```
REPOSITORY_ROOT_USERNAME="username"
REPOSITORY_ROOT_PASSWORD="password"

REPOSITORY_HOST="database"
REPOSITORY_DATABASE="gamebase"
REPOSITORY_USERNAME="username"
REPOSITORY_PASSWORD="password"
REPOSITORY_PORT="3306"
REPOSITORY_CHARSET="utf8mb4"
REPOSITORY_EXPOSE_PORT="3307"
```

`REPOSITORY_PORT`: internal container port

`REPOSITORY_EXPOSE_PORT`: host port mapped to container

For `Docker`, ensure `REPOSITORY_HOST` has the **same value** as the name of the **database container**. Ex: `REPOSITORY_HOST="mariadb"`

#### Encryption Keys

You may keep the default values, but generating your own keys is recommended.

Access:

```
http://localhost:{$NGINX_EXPOSE_PORT}/pages/login
```

Then click on:

- `Defuse Encryption`
- `Sodium Encryption`
- `Jwt Encryption`

Copy the `Key` values into:

```
DEFUSE_PHP_ENCRYPTION_KEY=
SODIUM_CRYPTO_SECRETBOX_KEY=
JWT_SECRET=
```

#### Redis

```
REDIS_SCHEME=redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_EXPOSE_PORT=6380
```

`REDIS_PORT`: internal container port

`REDIS_EXPOSE_PORT`: host port mapped to container

For `Docker`, ensure `REDIS_HOST` has the **same value** as the name of the **redis container**. Ex: `REDIS_HOST="redis"`

### 3. Create the Database

Access

```
http://localhost:{$NGINX_EXPOSE_PORT}/pages/login
```

To make **first login**, use `REPOSITORY_ROOT_USERNAME` and `REPOSITORY_ROOT_PASSWORD` provided on the `.env.enviroment.machine.example` file.

After that, do the following steps:

1. Click on **PDO Database**
2. Verify if the database was created. If not, click on **Create**.
3. Click on **Phinx**
4. Ensure the status is **OK**. It will only work if the database exists.

### 4. Generate OpenAPI Documentation

Run the following command to generate the OpenAPI documentation:

```
/configurations/openapi/startup/startup.sh
```

On the root folder of the project.

### 5. Run the project

Run the scripts to control the Docker containers:

```
/configurations/docker/scripts/development/build.sh
/configurations/docker/scripts/development/kill.sh
/configurations/docker/scripts/development/logs.sh
```

On the root folder of the project.

## Logs

The logs folder is located in:

```
/logs/
```

On the root folder of the project.

## Composer Commands

All commands are defined in the `scripts` section of `composer.json`.

---

***Made with ❤️ by Marcus Vinicius Reis Gonçalves***