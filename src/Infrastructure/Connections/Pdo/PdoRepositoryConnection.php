<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo;

use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\Options\PdoRepositoryConnectionOptions;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class PdoRepositoryConnection
{
    private \PDO $pdo;

    public function __construct(PdoRepositoryConnectionOptions $options)
    {
        $adapter = DotenvEnvironment::get("REPOSITORY_ADAPTER");
        $host = DotenvEnvironment::get("REPOSITORY_HOST");
        $dsn = "$adapter:host=$host;";

        if ($options->getUseDatabase()) {
            $database = DotenvEnvironment::get("REPOSITORY_DATABASE");
            $dsn .= "dbname=$database;";
        }

        $username = DotenvEnvironment::get("REPOSITORY_USERNAME");
        $password = DotenvEnvironment::get("REPOSITORY_PASSWORD");

        $this->pdo = new \PDO(
            $dsn,
            $username,
            $password,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );
    }

    public static function make(
        PdoRepositoryConnectionOptions $options = new PdoRepositoryConnectionOptions(
            true
        )
    ): PdoRepositoryConnection {
        return new self(
            $options
        );
    }

    public function get(): \PDO
    {
        return $this->pdo;
    }
}
