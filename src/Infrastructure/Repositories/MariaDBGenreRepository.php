<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;

/**
 * The MariaDB Genre repository.
 */
class MariaDBGenreRepository implements GenreRepositoryInterface
{
    /**
     * @var PDO $pdo The object to make database actions.
     */
    private PDO $pdo;

    /**
     * The MariaDB Genre repository constructor.
     * @param PDO $pdo The object to make database actions.
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Inserts a new Genre into the repository
     * @param Genre $genre The Genre to be inserted.
     * @return Genre A copy of the Genre inserted.
     * @throws PDOException Throwed if a database error occurs.
     */
    public function insert(Genre $genre): Genre
    {
        try {
            $this->pdo->beginTransaction();

            $name = $genre->getName();

            $insertStatement = $this->pdo->prepare('INSERT INTO genre (name) VALUES (:name);');
            $insertStatement->execute([':name' => $name]);

            $lastInsertId = intval($this->pdo->lastInsertId());

            $selectGameStatement = $this->pdo->prepare('SELECT * FROM genre WHERE id = :id;');
            $selectGameStatement->execute([':id' => $lastInsertId]);

            $genreFetchResult = $selectGameStatement->fetch();

            $this->pdo->commit();

            $newGenre = new Genre();
            $newGenre->setId($genreFetchResult['id']);
            $newGenre->setName($genreFetchResult['name']);

            return $newGenre;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Updates a Genre already created in the repository.
     * @param Genre $genre The genre data to update.
     * @return bool The success flag.
     * @throws PDOException Throwed if a database error occurs.
     */
    public function update(Genre $genre): bool
    {
        try {
            $id = $genre->getId();
            $name = $genre->getName();

            $statement = $this->pdo->prepare('UPDATE genre SET name = :name WHERE id = :id;');

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
     * Deletes a Genre from the repository by its id.
     * @param int $id The Genre id to be deleted.
     * @return bool The success flag.
     * @throws PDOException Throwed if a database error occurs.
     */
    public function delete(int $id): bool
    {
        return false;
    }

    /**
     * Finds a Genre in the repository by its id.
     * @param int $id The Genre id.
     * @return Genre|null Returns the Genre if it finds, else returns null.
     * @throws PDOException Throwed if a database error occurs.
     */
    public function findById(int $id): Genre|null
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM genre WHERE id = :id;');
            $statement->execute([':id' => $id]);

            $result = $statement->fetch();
            if ($result === false) {
                return null;
            }

            $genre = new Genre();
            $genre->setId($result['id']);
            $genre->setName($result['name']);
            return $genre;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Find all the Genre registers in the repository.
     * @return array A list of genres.
     * @throws PDOException Throwed if a database error occurs.
     */
    public function findAll(): array
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM genre;');
            $statement->execute();

            $result = $statement->fetchAll();

            $genres = [];

            foreach ($result as $row) {
                $genre = new Genre();
                $genre->setId($row['id']);
                $genre->setName($row['name']);
                $genres[] = $genre;
            }

            return $genres;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Checks if the name passed already exists in the repository.
     * @param string $name The name to check.
     * @return bool True if already exists, else false.
     * @throws PDOException Throwed if a database error occurs.
     */
    public function hasDuplicatedNames(string $name): bool
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM genre WHERE name = :name;');
            $statement->execute([':name' => $name]);

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
