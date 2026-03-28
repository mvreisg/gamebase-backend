# Gamebase-Backend

RESTful backend for managing a game database, built with modern PHP practices and a DDD-inspired structure.

This project is designed as a study and portfolio application focusing on:

- Layered architecture
- Secure authentication and encryption
- Containerized development
- Scalable environment configuration

---

## Features

- REST API
- JWT Authentication
- Redis caching
- Database migrations with Phinx
- Encryption (Sodium / Defuse)
- Docker-first development
- Unit testing with PHPUnit
- Environment variables with Dotenv

---

## Tech Stack

- PHP 8.4
- Nginx
- Slim Framework
- PHP-DI
- MariaDB
- Redis
- Docker
- Composer
- PHPUnit
- Phinx
- Dotenv

---

## Project Architecture

The project loosely follows DDD principles:

- **Domain**
- **Application**
- **Infrastructure**
- **Presentation**

---

## Project Status

🚧 Active development.

---

## License

GPL-3.0

---

### Documentation

🚧 *In the future*

---

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

*The `run.development.docker.sh` script is available to ease the `Docker` running.*

---

### 2. Environment Variables

#### Timezone

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

#### Encryption Keys

You may keep the default values, but generating your own keys is recommended.

Access:

```
http://localhost:${NGINX_EXPOSE_PORT}/pages/index.php
```

Then click on:

- `Get PHP Defuse Encryption Key`
- `Get Sodium Encryption Key`

Copy the values into:

```
DEFUSE_PHP_ENCRYPTION_KEY=
SODIUM_CRYPTO_SECRETBOX_KEY=
```

⚠️ Keep both values secret (even if unused).

#### JWT Secret

```
JWT_SECRET="your-secret-key"
```

#### Redis

```
REDIS_SCHEME=redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_EXPOSE_PORT=6380
```

---

### 3. Create the Database

Access

```
http://localhost:{$NGINX_EXPOSE_PORT}/pages/index.php
```

Steps:

1. Click on **PDO Database → Create**
2. Verify the database was created
3. Click on **Phinx Startup**
4. Ensure the status is **OK**

---

### Composer Commands

All commands are defined in the `scripts` section of `composer.json`.

---

*Made with ❤️ by Marcus Vinicius Reis Gonçalves*