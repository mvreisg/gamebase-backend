<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb;

use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\Collection\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\GameGenre;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class MariaDbGameGenreRepository implements GameGenreRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function insert(GameGenre $gameGenre): GameGenre
    {
        try {
            $this->connection->beginTransaction();

            $genreId = $gameGenre->getGenre()->getId()->getValue();
            $gameId = $gameGenre->getGame()->getId()->getValue();

            $insertStatement = $this->connection->prepare(
                "INSERT INTO game_genre (
                    genre_id, 
                    game_id
                ) 
                VALUES (
                    :genreId, 
                    :gameId
                );"
            );

            $insertStatement->execute([
                ":genreId" => $genreId,
                ":gameId" => $gameId
            ]);

            $lastInsertedId = intval(
                $this->connection->lastInsertId()
            );

            $selectStatement = $this->connection->prepare(
                "SELECT 
                    gage.id AS gage_id,
                    gage.game_id AS gage_game_id,
                    gage.genre_id AS gage_genre_id,
                    gm.id AS gm_id,
                    gm.name AS gm_name,
                    gm.is_active AS gm_is_active,
                    ge.id AS ge_id,
                    ge.name AS ge_name,
                    ge.is_active AS ge_is_active
                FROM 
                    game_genre gage
                JOIN
                    game gm
                ON
                    gage.game_id = gm.id
                JOIN
                    genre ge
                ON
                    gage.genre_id = ge.id
                WHERE 
                    gage.id = :id;"
            );

            $selectStatement->execute([
                ":id" => $lastInsertedId
            ]);

            $fetchResult = $selectStatement->fetch();

            $this->connection->commit();

            $value = GameGenre::create(
                Id::create(
                    $fetchResult["gage_id"]
                ),
                Game::create(
                    Id::create(
                        $fetchResult["gm_id"]
                    ),
                    Name::create(
                        $fetchResult["gm_name"]
                    ),
                    $fetchResult["gm_is_active"]
                ),
                Genre::create(
                    Id::create(
                        $fetchResult["ge_id"]
                    ),
                    Name::create(
                        $fetchResult["ge_name"]
                    ),
                    $fetchResult["ge_is_active"]
                ),
            );
            return $value;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function update(GameGenre $gameGenre): bool
    {
        try {
            $id = $gameGenre->getId()->getValue();
            $gameId = $gameGenre->getGame()->getId()->getValue();
            $genreId = $gameGenre->getGenre()->getId()->getValue();

            $statement = $this->connection->prepare(
                "UPDATE 
                    game_genre 
                SET 
                    genre_id = :genreId, 
                    game_id = :gameId 
                WHERE 
                    id = :id;"
            );

            $statement->execute([
                ":id" => $id,
                ":gameId" => $gameId,
                ":genreId" => $genreId
            ]);

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id): bool
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "DELETE FROM
                    game_genre
                WHERE
                    id = :id;"
            );

            $statement->execute([
                ":id" => $idValue
            ]);

            $wasUpdated = $statement->rowCount() > 0;
            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): ?GameGenre
    {
        try {
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT 
                    gage.id AS gage_id,
                    gage.game_id AS gage_game_id,
                    gage.genre_id AS gage_genre_id,
                    gm.id AS gm_id,
                    gm.name AS gm_name,
                    gm.is_active AS gm_is_active,
                    ge.id AS ge_id,
                    ge.name AS ge_name,
                    ge.is_active AS ge_is_active
                FROM 
                    game_genre gage
                JOIN
                    game gm
                ON
                    gage.game_id = gm.id
                JOIN
                    genre ge
                ON
                    gage.genre_id = ge.id
                WHERE 
                    gage.id = :id;"
            );

            $statement->execute([
                ":id" => $idValue
            ]);

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                return null;
            }

            $value = GameGenre::create(
                Id::create(
                    $fetchResult["gage_id"]
                ),
                Game::create(
                    Id::create(
                        $fetchResult["gm_id"]
                    ),
                    Name::create(
                        $fetchResult["gm_name"]
                    ),
                    $fetchResult["gm_is_active"]
                ),
                Genre::create(
                    Id::create(
                        $fetchResult["ge_id"]
                    ),
                    Name::create(
                        $fetchResult["ge_name"]
                    ),
                    $fetchResult["ge_is_active"]
                ),
            );
            return $value;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): ?GameGenreCollection
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT 
                    gage.id AS gage_id,
                    gage.game_id AS gage_game_id,
                    gage.genre_id AS gage_genre_id,
                    gm.id AS gm_id,
                    gm.name AS gm_name,
                    gm.is_active AS gm_is_active,
                    ge.id AS ge_id,
                    ge.name AS ge_name,
                    ge.is_active AS ge_is_active
                FROM 
                    game_genre gage
                JOIN
                    game gm
                ON
                    gage.game_id = gm.id
                JOIN
                    genre ge
                ON
                    gage.genre_id = ge.id;"
            );

            $statement->execute();

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) === 0) {
                return null;
            }

            $gameGenres = new GameGenreCollection();
            foreach ($fetchResult as $row) {
                $value = GameGenre::create(
                    Id::create(
                        $fetchResult["gage_id"]
                    ),
                    Game::create(
                        Id::create(
                            $fetchResult["gm_id"]
                        ),
                        Name::create(
                            $fetchResult["gm_name"]
                        ),
                        $fetchResult["gm_is_active"]
                    ),
                    Genre::create(
                        Id::create(
                            $fetchResult["ge_id"]
                        ),
                        Name::create(
                            $fetchResult["ge_name"]
                        ),
                        $fetchResult["ge_is_active"]
                    ),
                );
                $gameGenres->add($value);
            }

            return $gameGenres;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function checkIfExists(Id $id): bool
    {
        try {
            $alias = "number_of_ids";
            $idValue = $id->getValue();

            $statement = $this->connection->prepare(
                "SELECT
                    COUNT(*) 
                    AS
                    $alias
                FROM
                    game_genre
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
}
