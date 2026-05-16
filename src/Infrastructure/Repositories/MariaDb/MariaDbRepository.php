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

    public function exists(string $database): bool
    {
        $statement = $this->connection->prepare("
            SELECT COUNT(SCHEMA_NAME) AS count FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :database
        ");

        $statement->execute([
            ":database" => $database
        ]);

        $fetchResult = $statement->fetch();

        $count = intval($fetchResult["count"]);

        return $count > 0;
    }

    public function create(string $database): bool
    {
        return $this->connection->exec("CREATE DATABASE `$database`");
    }

    public function drop(string $database): bool
    {
        return $this->connection->exec("DROP DATABASE `$database`");
    }
}
