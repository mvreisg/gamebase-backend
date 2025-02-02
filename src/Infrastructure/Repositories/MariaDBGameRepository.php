<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;

/**
 * The MariaDB Game repository class.
 */
class MariaDBGameRepository implements GameRepositoryInterface
{
    /**
     * @var PDO $pdo The PDO object to make database actions.
     */
    private PDO $pdo;

    /**
     * The MariaDB Game repository class constructor.
     * @param PDO $pdo The PDO object to make dabatase actions.
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Inserts a Game into the repository.
     * @param Game $game The Game object containing the data to be inserted into the repository.
     * @return Game The inserted Game object clone.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function insert(Game $game): Game
    {
        try {
            $this->pdo->beginTransaction();

            $name = $game->getName();

            $insertStatement = $this->pdo->prepare('INSERT INTO game (name) VALUES (:name);');
            $insertStatement->execute([':name' => $name]);

            $lastInsertId = intval($this->pdo->lastInsertId());

            $selectStatement = $this->pdo->prepare('SELECT * FROM game WHERE id = :id;');
            $selectStatement->execute([':id' => $lastInsertId]);

            $gameFetchResult = $selectStatement->fetch();

            $this->pdo->commit();

            $newGame = new Game();
            $newGame->setId($gameFetchResult['id']);
            $newGame->setName($gameFetchResult['name']);

            return $newGame;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Updates a Game register in the Game repository.
     * @param Game $game The Game object containing the data to be updated into the repository.
     * @return bool Returns the success flag.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function update(Game $game): bool
    {
        $id = $game->getId();
        $name = $game->getName();

        try {
            $statement = $this->pdo->prepare('UPDATE game SET name = :name WHERE id = :id;');

            $statement->execute([
                ':name' => $name,
                ':id' => $id
            ]);

            $wasItSuccessful = $statement->rowCount() > 0;
            return $wasItSuccessful;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes a Game registed in the Game repository by the id.
     * @param int $id The respective id of the Game register that is wanted to be deleted.
     * @return bool Returns the success flag.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function delete(int $id): bool
    {
        return false;
    }

    /**
     * Finds a Game register in the Game repository by its respective id and returns their Game object if it was found.
     * @param int $id The id of the Game register that is wanted to be found.
     * @return Game|null Returns the Game object if id is founded, else returns null.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function findById(int $id): Game|null
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM game WHERE id = :id;');
            $statement->execute([':id' => $id]);

            $result = $statement->fetch();

            if ($result === false) {
                return null;
            }

            $game = new Game();
            $game->setId($result['id']);
            $game->setName($result['name']);

            return $game;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds all the Game registers in the repository.
     * @return array Returns all Games registers found in the Game repository in a list.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function findAll(): array
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM game;');
            $statement->execute();
            $result = $statement->fetchAll();

            $games = [];
            foreach ($result as $row) {
                $game = new Game();
                $game->setId($row['id']);
                $game->setName($row['name']);
                $games[] = $game;
            }

            return $games;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Checks if a register with the name already exists in the repository.
     * @param string $name The Game name.
     * @return bool Returns true if the register already exists, else false.
     * @throws PDOException Throwed if a PDO database action error occurs.
     */
    public function hasDuplicatedNames(string $name): bool
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM game WHERE name = :name;');
            $statement->execute([':name' => $name]);

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
