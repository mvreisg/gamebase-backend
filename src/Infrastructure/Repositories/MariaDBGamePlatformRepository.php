<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;

/**
 * MariaDB Game Platform repository class.
 */
class MariaDBGamePlatformRepository implements GamePlatformRepositoryInterface
{
    /**
     * @var PDO $pdo The database connection class object.
     */
    private PDO $pdo;

    /**
     * MariaDB Game Platform repository class constructor.
     * @param PDO $pdo The database connection class object.
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Inserts a new Game Platform and returns a copy of the object.
     * @param GamePlatform $gamePlatform The object to be inserted.
     * @return GamePlatform The copy of the inserted object.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function insert(GamePlatform $gamePlatform): GamePlatform
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new DatabaseTransactionCreationFailureException('Ocorreu um erro ao criar a transação!');
            }

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    game_platform 
                        (platform_id, game_id) 
                VALUES 
                    (:platformId, :gameId);'
            );
            if ($insertStatement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de inserção!');
            }

            $wasTheInsertStatementExecutionSuccessful = $insertStatement->execute([
                ':platformId' => $gamePlatform->getPlatformId(),
                ':gameId' => $gamePlatform->getGameId()
            ]);
            if ($wasTheInsertStatementExecutionSuccessful === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de inserção!');
            }

            $lastInsertedId = $this->pdo->lastInsertId();
            $lastInsertedId = intval($lastInsertedId);

            $selectStatement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game_platform 
                WHERE 
                    id = :id;'
            );
            if ($selectStatement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a transação de busca!');
            }

            $wasTheSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);
            if ($wasTheSelectStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a transação de busca!');
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new DatabaseFetchFailureException('Ocorreu um erro ao realizar a busca dos dados!');
            }

            $this->pdo->commit();

            $gamePlatform = new GamePlatform();
            $gamePlatform->setId($fetchResult['id']);
            $gamePlatform->setPlatformId($fetchResult['platform_id']);
            $gamePlatform->setGameId($fetchResult['game_id']);

            return $gamePlatform;
        } catch (DatabaseTransactionCreationFailureException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Updates an existing Game Platform, returning the success flag.
     * @param GamePlatform $gamePlatform The object to be updated.
     * @return bool The success flag.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function update(GamePlatform $gamePlatform): bool
    {
        $id = $gamePlatform->getId();
        $platformId = $gamePlatform->getPlatformId();
        $gameId = $gamePlatform->getGameId();

        try {
            $statement = $this->pdo->prepare(
                'UPDATE 
                    game_platform 
                SET 
                    platform_id = :platformId,
                    game_id = :gameId
                WHERE 
                    id = :id;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de atualização!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':platformId' => $platformId,
                ':gameId' => $gameId,
                ':id' => $id
            ]);

            return $wasTheStatementSuccessfullyExecuted;
        } catch (DatabaseStatementCreationFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes an existing Game Platform, returning the success flag.
     * @param GamePlatform $gamePlatform The object containing the data necessary for the deletion.
     * @return bool The success flag.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function delete(GamePlatform $gamePlatform): bool
    {
        $id = $gamePlatform->getId();

        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                    game_platform
                WHERE
                    id = :id'
            );

            $wasItSuccessful = $statement->execute([
                'id' => $id,
            ]);

            return $wasItSuccessful;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes all Game Platforms with the Game id.
     * @param GamePlatform $gamePlatform The object containing the Game id.
     * @return bool The success flag.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function deleteAllByGameId(GamePlatform $gamePlatform): bool
    {
        $gameId = $gamePlatform->getGameId();

        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                    game_platform
                WHERE
                    game_id = :gameId;'
            );

            $wasItSuccessful = $statement->execute([
                'gameId' => $gameId
            ]);

            return $wasItSuccessful;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Find an Game Platform by its id.
     * @param int $id The game Platform id.
     * @return GamePlatform The found Game Platform, else null.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function findById(int $id): GamePlatform|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    *
                FROM
                    game_platform
                WHERE
                    id = :id;'
            );

            $statement->execute([
                ':id' => $id
            ]);

            $result = $statement->fetch();

            if ($result == false) {
                return null;
            }

            $gamePlatform = new GamePlatform();
            $gamePlatform->setId($result['id']);
            $gamePlatform->setPlatformId($result['platform_id']);
            $gamePlatform->setGameId($result['game_id']);

            return $gamePlatform;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Find all Game Platforms.
     * @return array A list containing the Game Platforms.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function findAll(): array
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT * FROM game_platform;'
            );

            $statement->execute();

            $result = $statement->fetchAll();

            $gamePlatforms = [];

            foreach ($result as $line) {
                $gamePlatform = new GamePlatform();
                $gamePlatform->setId($line['id']);
                $gamePlatform->setPlatformId($line['platform_id']);
                $gamePlatform->setGameId($line['game_id']);

                $gamePlatforms[] = $gamePlatform;
            }

            return $gamePlatforms;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Returns all Game Platforms with the Game id.
     * @param int $gameId The Game id.
     * @return array A list containing the Game Platforms.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function findAllGamePlatformsByGameId(int $gameId): array
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game_platform 
                WHERE 
                    game_id = :gameId;'
            );

            $statement->execute([
                ':gameId' => $gameId
            ]);

            $result = $statement->fetchAll();
            $gamePlatforms = [];
            foreach ($result as $row) {
                $gamePlatform = new GamePlatform();
                $gamePlatform->setId($row['id']);
                $gamePlatform->setGameId($row['game_id']);
                $gamePlatform->setPlatformId($row['platform_id']);
                $gamePlatforms[] = $gamePlatform;
            }

            return $gamePlatforms;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Returns all Game Platforms and Game data intersected by Game id.
     * @return array A list containing the Game Platforms.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function innerJoinBetweenGameAndGamePlatformByGameId(): array
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT
                    game_platform.id AS id,
                    game_platform.game_id AS game_id,
                    game_platform.platform_id AS platform_id
                FROM
                    game
                INNER JOIN
                    game_platform
                ON
                    game.id = game_platform.game_id;'
            );
            $statement->execute();
            $result = $statement->fetchAll();

            $gamePlatforms = [];
            foreach ($result as $row) {
                $gamePlatform = new GamePlatform();
                $gamePlatform->setId($row['id']);
                $gamePlatform->setGameId($row['game_id']);
                $gamePlatform->setPlatformId($row['platform_id']);
                $gamePlatforms[] = $gamePlatform;
            }

            return $gamePlatforms;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
