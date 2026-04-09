<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb;

use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Collection\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Genre\Repository\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class MariaDbGenreRepository implements GenreRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function insert(Genre $genre): Genre
    {
        try {
            $this->connection->beginTransaction();

            $name = $genre->getName()->getValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $genre->getIsActive()
            );

            $insertStatement = $this->connection->prepare(
                "INSERT INTO 
                    genre (
                        name,
                        is_active
                    ) 
                VALUES 
                    (
                        :name,
                        :isActive
                    );"
            );

            $insertStatement->execute([
                ":name" => $name,
                ":isActive" => $isActive
            ]);

            $lastInsertedId = intval(
                $this->connection->lastInsertId()
            );

            $selectStatement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    genre 
                WHERE 
                    id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $return = new Genre(
                Id::create($fetchResult["id"]),
                Name::create($fetchResult["name"]),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
            return $return;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function update(Genre $genre): bool
    {
        try {
            $id = $genre->getId()->getValue();
            $name = $genre->getName()->getValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $isActive = intval(
                $genre->getIsActive()
            );

            $statement = $this->connection->prepare(
                "UPDATE 
                    genre 
                SET 
                    name = :name, 
                    is_active = :isActive 
                WHERE 
                    id = :id;"
            );

            $statement->execute([
                ":name" => $name,
                ":id" => $id,
                ":isActive" => $isActive
            ]);

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        try {
            $idValue = $id->getValue();

            /* MariaDB bool limitation forces casting bool to int
             * to send to the database.
             */
            $intIsActive = intval($isActive);

            $statement = $this->connection->prepare(
                "UPDATE
                    genre
                SET
                    is_active = :isActive
                WHERE
                    id = :id
                AND
                    is_active <> :isActive;"
            );

            $statement->execute([
                ":id" => $idValue,
                ":isActive" => $intIsActive
            ]);

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): ?Genre
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    genre 
                WHERE 
                    id = :id;"
            );

            $statement->execute([
                ":id" => $idValue
            ]);

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                return null;
            }

            $return = new Genre(
                Id::create($fetchResult["id"]),
                Name::create($fetchResult["name"]),
                /* MariaDB stores bool as int values so a casting
                 * here is needed.
                 */
                boolval(
                    $fetchResult["is_active"]
                )
            );
            return $return;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): ?GenreCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    * 
                FROM 
                    genre;"
            );

            $statement->execute();

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) === 0) {
                return null;
            }

            $genres = new GenreCollection();
            foreach ($fetchResult as $row) {
                $value = new Genre(
                    Id::create($row["id"]),
                    Name::create($row["name"]),
                    /* MariaDB stores bool as int values so a casting
                    * here is needed.
                    */
                    boolval(
                        $row["is_active"]
                    )
                );
                $genres->add($value);
            }
            return $genres;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkIfExists(Id $id): bool
    {
        try {
            $idValue = $id->getValue();

            $alias = "number_of_ids";
            $statement = $this->connection->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    $alias
                FROM
                    genre
                WHERE
                    id = :id;"
            );

            $statement->execute([
                ":id" => $idValue
            ]);

            $fetchResult = $statement->fetch();
            $numberOfIds = intval(
                $fetchResult[
                    $alias
                ]
            );
            return $numberOfIds > 0;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkDuplicatedNames(?Id $id = null, Name $name): bool
    {
        try {
            $idValue = $id ? $id->getValue() : null;
            $nameValue = $name->getValue();

            $alias = "number_of_names";
            if ($idValue === null) {
                $statement = $this->connection->prepare(
                    "SELECT 
                        COUNT(*)
                        AS
                        $alias
                    FROM 
                        genre 
                    WHERE 
                        name = :name;"
                );

                $statement->execute([
                    ":name" => $nameValue
                ]);
            } else {
                $statement = $this->connection->prepare(
                    "SELECT 
                        COUNT(*)
                        AS
                        $alias
                    FROM 
                        genre 
                    WHERE 
                        name = :name
                    AND
                        id != :id;"
                );

                $statement->execute([
                    ":name" => $nameValue,
                    ":id" => $idValue
                ]);
            }

            $fetchResult = $statement->fetch();
            $numberOfNames = intval(
                $fetchResult[
                    $alias
                ]
            );
            return $numberOfNames > 0;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
