<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb;

use Mvreisg\GamebaseBackend\Domain\Shared\Interface\DatabaseRepositoryInterface;

class MariaDbRepository implements DatabaseRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function exists(string $databaseName): bool
    {
        $statement = $this->connection->prepare("
            SELECT COUNT(SCHEMA_NAME) AS count FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :databaseName
        ");

        $statement->execute([
            ":databaseName" => $databaseName
        ]);

        $fetchResult = $statement->fetch();

        return $fetchResult["count"] > 0;
    }

    public function create(string $databaseName): void
    {
        $this->connection->exec("CREATE DATABASE `$databaseName`");
    }

    public function drop(string $databaseName): void
    {
        $this->connection->exec("DROP DATABASE `$databaseName`");
    }
}
