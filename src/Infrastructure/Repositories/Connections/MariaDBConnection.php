<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Connections;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Connections\Exceptions\MariaDBConnectionException;
use PDO;

class MariaDBConnection
{
    public static function get(): PDO
    {
        try {
            $dsn =
                DotenvEnvironment::get('REPOSITORY_ADAPTER') .
                ":host=" .
                DotenvEnvironment::get('REPOSITORY_HOST') .
                ";dbname=" .
                DotenvEnvironment::get('REPOSITORY_DATABASE');

            $username = DotenvEnvironment::get('REPOSITORY_USERNAME');

            $password = DotenvEnvironment::get('REPOSITORY_PASSWORD');

            return new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (\Throwable $e) {
            throw new MariaDBConnectionException(
                "MariaDB Connection error: {$e->getMessage()}",
                $e
            );
        }
    }

    public static function getWithoutDatabase(): PDO
    {
        try {
            $dsn =
                DotenvEnvironment::get('REPOSITORY_ADAPTER') .
                ":host=" .
                DotenvEnvironment::get('REPOSITORY_HOST');

            $username = DotenvEnvironment::get('REPOSITORY_USERNAME');

            $password = DotenvEnvironment::get('REPOSITORY_PASSWORD');

            return new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (\Throwable $e) {
            throw new MariaDBConnectionException(
                "MariaDB Connection error: {$e->getMessage()}",
                $e
            );
        }
    }
}
