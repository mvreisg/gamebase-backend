<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;

/**
 * MariaDB platform repository class.
 */
class MariaDBPlatformRepository implements PlatformRepositoryInterface
{
    /**
     * @var PDO $pdo The database conntection object.
     */
    private PDO $pdo;

    /**
     * MariaDB platform repository class constructor.
     * @param PDO $pdo The database conntection object.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Inserts a new Platform into the repository.
     * @param Platform $platform The Platform to be inserted.
     * @return Platform A copy of the inserted Platform.
     * @throws PDOException Throwed in case of database error.
     */    
    public function insert(Platform $platform): Platform
    {
        try {
            $this->pdo->beginTransaction();

            $name = $platform->getName();

            $insertStatement = $this->pdo->prepare('INSERT INTO platform (name) VALUES (:name);');
            $insertStatement->execute([':name' => $name]);

            $lastInsertId = intval($this->pdo->lastInsertId());

            $selectGameStatement = $this->pdo->prepare('SELECT * FROM platform WHERE id = :id;');
            $selectGameStatement->execute([':id' => $lastInsertId]);

            $genreFetchResult = $selectGameStatement->fetch();

            $this->pdo->commit();

            $newPlatform = new Platform();
            $newPlatform->setId($genreFetchResult['id']);
            $newPlatform->setName($genreFetchResult['name']);

            return $newPlatform;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Updates an existing Platform in the repository.
     * @param Platform $platform The Platform data to be updated.
     * @return bool The success flag.
     * @throws PDOException Throwed in case of database error.
     */    
    public function update(Platform $platform): bool
    {
        try {
            $id = $platform->getId();
            $name = $platform->getName();

            $statement = $this->pdo->prepare('UPDATE platform SET name = :name WHERE id = :id;');

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
     * Deletes an existing Platform in the repository.
     * @param int $id The id of the register to be deleted.
     * @return bool The success flag.
     * @throws PDOException Throwed in case of database error.
     */
    public function delete(int $id): bool
    {
        return false;
    }

    /**
     * Finds a Platform in the repository by its id.
     * @param int $id The id to search for.
     * @return Platform|null Returns the Platform if found, else returns null.
     * @throws PDOException Throwed in case of database error.
     */    
    public function findById(int $id): Platform|null
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM platform WHERE id = :id;');
            $statement->execute([':id' => $id]);

            $result = $statement->fetch();
            if ($result === false) {
                return null;
            }

            $platform = new Platform();
            $platform->setId($result['id']);
            $platform->setName($result['name']);
            return $platform;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Finds all Platforms in the repository.
     * @return array A list containing all the founded repositories.
     * @throws PDOException Throwed in case of database error.
     */    
    public function findAll(): array
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM platform;');
            $statement->execute();

            $result = $statement->fetchAll();

            $platforms = [];

            foreach ($result as $row) {
                $platform = new Platform();
                $platform->setId($row['id']);
                $platform->setName($row['name']);
                $platforms[] = $platform;
            }

            return $platforms;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Check if the name already exists in the repository.
     * @param string $name The name to be searched.
     * @return bool True if it already exists, else returns false.
     * @throws PDOException Throwed in case of database error.
     */    
    public function hasDuplicatedNames(string $name): bool
    {
        try {
            $statement = $this->pdo->prepare('SELECT * FROM platform WHERE name = :name;');
            $statement->execute([':name' => $name]);

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
