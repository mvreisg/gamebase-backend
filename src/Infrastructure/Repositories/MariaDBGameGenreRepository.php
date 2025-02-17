<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use Throwable;

/**
 * MariaDB Game Genre repository.
 */
class MariaDBGameGenreRepository implements GameGenreRepositoryInterface
{
    /**
     * @var PDO $pdo The database connection class object.
     */
    private PDO $pdo;

    /**
     * MariaDB game Genre repository class constructor.
     * @param PDO $pdo The database connection class object.
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Inserts a new Game Genre entity into the repository.
     * @param GameGenre $gameGenre The eneity to be inserted.
     * @return GameGenre a copy of the inserted entity.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function insert(GameGenre $gameGenre): GameGenre
    {
        $genreId = $gameGenre->getGenreId();
        $gameId = $gameGenre->getGameId();

        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new DatabaseTransactionCreationFailureException('Ocorreu um erro ao criar a transação!');
            }

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    game_genre 
                        (genre_id, game_id) 
                VALUES 
                    (:genreId, :gameId);'
            );
            if ($insertStatement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de inserção!');
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
                ':genreId' => $genreId,
                ':gameId' => $gameId
            ]);
            if ($wasTheInsertStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de inserção!');
            }

            $lastInsertedId = $this->pdo->lastInsertId();
            $lastInsertedId = intval($lastInsertedId);

            $selectStatement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game_genre 
                WHERE 
                    id = :id;'
            );
            if ($selectStatement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);
            if ($wasTheSelectStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de busca!');
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new DatabaseFetchFailureException('Ocorreu um erro ao buscar os valores!');
            }

            $this->pdo->commit();

            $gameGenre = new GameGenre();
            $gameGenre->setId($fetchResult['id']);
            $gameGenre->setGenreId($fetchResult['genre_id']);
            $gameGenre->setGameId($fetchResult['game_id']);

            return $gameGenre;
        } catch (DatabaseTransactionCreationFailureException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException | Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Updates an existing register of a Game Genre entity in the repository.
     * @param GameGenre $gameGenre The data to be updated.
     * @return bool The success flag.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function update(GameGenre $gameGenre): bool
    {
        try {
            $id = $gameGenre->getId();
            $gameId = $gameGenre->getGameId();
            $genreId = $gameGenre->getGenreId();

            $statement = $this->pdo->prepare(
                'UPDATE 
                    game_genre 
                SET 
                    genre_id = :genreId, 
                    game_id = :gameId 
                WHERE 
                    id = :id;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de atualização!');
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                ':id' => $id,
                ':gameId' => $gameId,
                ':genreId' => $genreId
            ]);

            return $wasTheStatementExecutionSuccessful;
        } catch (DatabaseStatementCreationFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes an existing register of a Game Genre entity in the repository.
     * @param GameGenre $gameGenre The data to be deleted.
     * @return bool The success flag.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function delete(GameGenre $gameGenre): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                    game_genre
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de exclusão!');
            }

            $id = $gameGenre->getId();

            $wasTheDeleteStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);
            if ($wasTheDeleteStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de exclusão!');
            }

            $numberOfRowsAffected = $statement->rowCount();
            $wasTheDeleteSuccessful = $numberOfRowsAffected > 0;

            return $wasTheDeleteSuccessful;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds a Game Genre register by its id.
     * @param int $id The id to find.
     * @return GameGenre the found Game Genre.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function findById(int $id): GameGenre|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game_genre 
                WHERE 
                    id = :id'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de busca!');
            }

            $result = $statement->fetch();
            if ($result === false) {
                return null;
            }

            $gameGenre = new GameGenre();
            $gameGenre->setId($result['id']);
            $gameGenre->setGameId($result['game_id']);
            $gameGenre->setGenreId($result['genre_id']);

            return $gameGenre;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds all Game Genre registers
     * @return array A list of all the Game Genres.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function findAll(): array
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    game_genre'
            );
            if ($statement === false){
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute();
            if ($wasTheStatementSuccessfullyExecuted === false){
                throw new DatabaseStatementExecutionFailureException('Ocorreu um erro ao executar a declaração de busca!');
            }

            $result = $statement->fetchAll();
            if ($result === false) {
                return [];
            }

            $gameGenres = [];

            foreach ($result as $row) {
                $gameGenre = new GameGenre();
                $gameGenre->setId($row['id']);
                $gameGenre->setGameId($row['game_id']);
                $gameGenre->setGenreId($row['genre_id']);

                $gameGenres[] = $gameGenre;
            }

            return $gameGenres;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }
}
