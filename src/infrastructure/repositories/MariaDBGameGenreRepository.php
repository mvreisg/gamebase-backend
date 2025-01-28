<?php
namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use Exception;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
    
class MariaDBGameGenreRepository implements GameGenreRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(GameGenre $gameGenre): GameGenre
    {
        $genreId = $gameGenre->getGenreId();
        $gameId = $gameGenre->getGameId();

        $newGameGenre = null;
        try {
            $statement = $this->pdo->prepare("INSERT INTO game_genre (genre_id, game_id) VALUES (:genreId, :gameId);");
            $statement->execute([
                ":genreId" => $genreId,
                ":gameId" => $gameId
            ]);

            $lastInsertId = intval($this->pdo->lastInsertId());
            $statement = $this->pdo->prepare("SELECT * FROM game_genre WHERE id = :id;");
            $statement->execute([":id" => $lastInsertId]);
            $result = $statement->fetch();

            $newGameGenre = new GameGenre();
            $newGameGenre->setId($result["id"]);
            $newGameGenre->setGenreId($result["genre_id"]);
            $newGameGenre->setGameId($result["game_id"]);
        } catch (PDOException $e) {
            throw $e;
        }

        return $newGameGenre;
    }

    public function edit(GameGenre $gameGenre): bool
    {
        try {
            $gameId = $gameGenre->getGameId();
            $statement = $this->pdo->prepare("UPDATE game_genre SET genre_id = :genreId WHERE game_id = :gameId;");
            $wasItSuccessful = $statement->execute([":gameId" => $gameId]);
            return $wasItSuccessful;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function delete(GameGenre $gameGenre): bool
    {
        try {
            $statement = $this->pdo->prepare(
                "DELETE FROM
                        game_genre
                    WHERE
                        game_id = :gameId
                    AND
                        genre_id = :genreId;"
            );

            $gameId = $gameGenre->getGameId();
            $genreId = $gameGenre->getGenreId();

            $wasItSuccessful = $statement->execute([
                "gameId" => $gameId,
                "genreId" => $genreId
            ]);

            return $wasItSuccessful;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function deleteAllByGameId(GameGenre $gameGenre): bool
    {
        try {
            $statement = $this->pdo->prepare(
                "DELETE FROM
                        game_genre
                    WHERE
                        game_id = :gameId;"
            );

            $gameId = $gameGenre->getGameId();

            $wasItSuccessful = $statement->execute(["gameId" => $gameId]);
            return $wasItSuccessful;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function findAllGameGenresByGameId(int $gameId): array
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM game_genre WHERE game_id = :gameId;");
            $statement->execute([":gameId" => $gameId]);

            $result = $statement->fetchAll();
            $gameGenres = [];
            foreach ($result as $row) {
                $gameGenre = new GameGenre();
                $gameGenre->setId($row["id"]);
                $gameGenre->setGameId($row["game_id"]);
                $gameGenre->setGenreId($row["genre_id"]);
                $gameGenres[] = $gameGenre;
            }

            return $gameGenres;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function innerJoinBetweenGameAndGameGenreByGameId(): array
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT
                        game_genre.id AS id,
                        game_genre.game_id AS game_id,
                        game_genre.genre_id AS genre_id
                    FROM
                        game
                    INNER JOIN
                        game_genre
                    ON
                        game.id = game_genre.game_id;"
            );
            $statement->execute();
            $result = $statement->fetchAll();

            $gameGenres = [];
            foreach ($result as $row) {
                $gameGenre = new GameGenre();
                $gameGenre->setId($row["id"]);
                $gameGenre->setGameId($row["game_id"]);
                $gameGenre->setGenreId($row["genre_id"]);
                $gameGenres[] = $gameGenre;
            }

            return $gameGenres;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
