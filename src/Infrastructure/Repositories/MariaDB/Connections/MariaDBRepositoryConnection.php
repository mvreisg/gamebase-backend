<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class MariaDBRepositoryConnection
{
    private static ?self $instance = null;
    private \PDO $connection;

    private static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function getDatabaseConnection(): \PDO
    {
        if (isset($this->connection) === false) {
            $dsn =
            DotenvEnvironment::get("REPOSITORY_ADAPTER") .
            ":host=" .
            DotenvEnvironment::get("REPOSITORY_HOST") .
            ";dbname=" .
            DotenvEnvironment::get("REPOSITORY_DATABASE");

            $username = DotenvEnvironment::get("REPOSITORY_USERNAME");

            $password = DotenvEnvironment::get("REPOSITORY_PASSWORD");

            $this->connection = new \PDO(
                $dsn,
                $username,
                $password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        }
        return $this->connection;
    }

    private function getRawConnection(): \PDO
    {
        if (isset($this->connection) === false) {
            $dsn =
            DotenvEnvironment::get("REPOSITORY_ADAPTER") .
            ":host=" .
            DotenvEnvironment::get("REPOSITORY_HOST");

            $username = DotenvEnvironment::get("REPOSITORY_USERNAME");

            $password = DotenvEnvironment::get("REPOSITORY_PASSWORD");

            $this->connection = new \PDO(
                $dsn,
                $username,
                $password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        }
        return $this->connection;
    }

    public static function get(): \PDO
    {
        return self::getInstance()->getDatabaseConnection();
    }

    public static function getRaw(): \PDO
    {
        return self::getInstance()->getRawConnection();
    }
}
