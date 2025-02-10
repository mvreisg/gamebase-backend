<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;

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

        $newGameGenre = null;
        try {
            $statement = $this->pdo->prepare('INSERT INTO game_genre (genre_id, game_id) VALUES (:genreId, :gameId);');
            $statement->execute([
                ':genreId' => $genreId,
                ':gameId' => $gameId
            ]);

            $lastInsertId = intval($this->pdo->lastInsertId());
            $statement = $this->pdo->prepare('SELECT * FROM game_genre WHERE id = :id;');
            $statement->execute([':id' => $lastInsertId]);
            $result = $statement->fetch();

            $newGameGenre = new GameGenre();
            $newGameGenre->setId($result['id']);
            $newGameGenre->setGenreId($result['genre_id']);
            $newGameGenre->setGameId($result['game_id']);
        } catch (PDOException $e) {
            throw $e;
        }

        return $newGameGenre;
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
            $wasItSuccessful = $statement->execute([
                ':id' => $id,
                ':gameId' => $gameId,
                ':genreId' => $genreId
            ]);
            return $wasItSuccessful;
        } catch (PDOException $e) {
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

            $id = $gameGenre->getId();

            $wasItSuccessful = $statement->execute([
                ':id' => $id
            ]);

            return $wasItSuccessful;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes all registers of Game Genre entity with the respective Game id binded to it.
     * @param GameGenre $gameGenre The data containing the Game id.
     * @return bool The success flag.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function deleteAllByGameId(GameGenre $gameGenre): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                        game_genre
                    WHERE
                        game_id = :gameId;'
            );

            $gameId = $gameGenre->getGameId();

            $wasItSuccessful = $statement->execute([':gameId' => $gameId]);
            return $wasItSuccessful;
        } catch (PDOException $e) {
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
            $statement = $this->pdo->prepare('SELECT * FROM game_genre WHERE id = :id');
            $statement->execute([
                ':id' => $id
            ]);
            $result = $statement->fetch();

            if ($result == false) {
                return null;
            }

            $gameGenre = new GameGenre();
            $gameGenre->setId($result['id']);
            $gameGenre->setGameId($result['game_id']);
            $gameGenre->setGenreId($result['genre_id']);

            return $gameGenre;
        } catch (PDOException $e) {
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
            $statement = $this->pdo->prepare('SELECT * FROM game_genre');
            $statement->execute();
            $result = $statement->fetchAll();

            if ($result == false) {
                return null;
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
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds all the Game Genres entities that contains the respective Game id.
     * @param int $gameId The Game id.
     * @return array A list containing the Game Genre entities.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function findAllGameGenresByGameId(int $gameId): array
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM game_genre WHERE game_id = :gameId;');
            $statement->execute([':gameId' => $gameId]);

            $result = $statement->fetchAll();
            $gameGenres = [];
            foreach ($result as $row) {
                $gameGenre = new GameGenre();
                $gameGenre->setId($row['id']);
                $gameGenre->setGameId($row['game_id']);
                $gameGenre->setGenreId($row['genre_id']);
                $gameGenres[] = $gameGenre;
            }

            return $gameGenres;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Makes a inner join between Game and Game Genre, returuning all registers with the Game id.
     * @return array A list containing the game Genre entities.
     * @throws PDOException Throwed if a database connection error occurs.
     */
    public function innerJoinBetweenGameAndGameGenreByGameId(): array
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT
                        game_genre.id AS id,
                        game_genre.game_id AS game_id,
                        game_genre.genre_id AS genre_id
                    FROM
                        game
                    INNER JOIN
                        game_genre
                    ON
                        game.id = game_genre.game_id;'
            );
            $statement->execute();
            $result = $statement->fetchAll();

            $gameGenres = [];
            foreach ($result as $row) {
                $gameGenre = new GameGenre();
                $gameGenre->setId($row['id']);
                $gameGenre->setGameId($row['game_id']);
                $gameGenre->setGenreId($row['genre_id']);
                $gameGenres[] = $gameGenre;
            }

            return $gameGenres;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
