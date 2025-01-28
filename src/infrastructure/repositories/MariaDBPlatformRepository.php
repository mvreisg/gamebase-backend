<?php
namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;
    
class MariaDBPlatformRepository implements PlatformRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Platform $platform): Platform
    {
        try {
            $this->pdo->beginTransaction();

            $name = $platform->getName();

            $insertStatement = $this->pdo->prepare("INSERT INTO platform (name) VALUES (:name);");
            $insertStatement->execute([":name" => $name]);

            $lastInsertId = intval($this->pdo->lastInsertId());

            $selectGameStatement = $this->pdo->prepare("SELECT * FROM platform WHERE id = :id;");
            $selectGameStatement->execute([":id" => $lastInsertId]);

            $genreFetchResult = $selectGameStatement->fetch();

            $this->pdo->commit();

            $newPlatform = new Platform();
            $newPlatform->setId($genreFetchResult["id"]);
            $newPlatform->setName($genreFetchResult["name"]);

            return $newPlatform;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function edit(Platform $platform): bool
    {
        try {
            $id = $platform->getId();
            $name = $platform->getName();
                
            $statement = $this->pdo->prepare("UPDATE platform SET name = :name WHERE id = :id;");

            $statement->execute([
                ":name" => $name,
                ":id" => $id
            ]);

            $wasItSuccessful = $statement->rowCount() > 0;
            return $wasItSuccessful;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        return false;
    }

    public function findById(int $id): Platform|null
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM platform WHERE id = :id;");
            $statement->execute([":id" => $id]);
                
            $result = $statement->fetch();
            if ($result === false) {
                return null;
            }

            $platform = new Platform();
            $platform->setId($result["id"]);
            $platform->setName($result["name"]);
            return $platform;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM platform;");
            $statement->execute();
                
            $result = $statement->fetchAll();

            $platforms = [];

            foreach ($result as $row) {
                $platform = new Platform();
                $platform->setId($row["id"]);
                $platform->setName($row["name"]);
                $platforms[] = $platform;
            }

            return $platforms;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function hasDuplicatedNames(string $name): bool
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM platform WHERE name = :name;");
            $statement->execute([":name" => $name]);
    
            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
