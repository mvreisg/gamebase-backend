<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories;

use PDO;
use PDOException;
use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;

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
            $wasTheTransactionCreationSuccessful = $this->pdo->beginTransaction();
            if ($wasTheTransactionCreationSuccessful === false) {
                throw new DatabaseTransactionCreationFailureException('Ocorreu um erro ao criar a transação!');
            }

            $name = $platform->getName();
            $isActive = $platform->getIsActive();

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    platform 
                        (
                            name,
                            is_active
                        ) 
                VALUES 
                        (
                            :name,
                            :isActive
                        );'
            );
            if ($insertStatement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a transação de inserção!'
                );
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([
                ':name' => $name,
                ':isActive' => $isActive
            ]);
            if ($wasTheInsertStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a transação de inserção!'
                );
            }

            $lastInsertedId = $this->pdo->lastInsertId();
            $lastInsertedId = intval($lastInsertedId);

            $selectStatement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    platform 
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
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new DatabaseFetchFailureException('Ocorreu um erro ao buscar os dados!');
            }

            $this->pdo->commit();

            $platform = new Platform();
            $platform->setId($fetchResult['id']);
            $platform->setName($fetchResult['name']);
            $platform->setIsActive($fetchResult['is_active']);

            return $platform;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e
        ) {
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
        $id = $platform->getId();
        $name = $platform->getName();
        $isActive = $platform->getIsActive();

        try {
            $statement = $this->pdo->prepare(
                'UPDATE 
                    platform 
                SET 
                    name = :name, 
                    is_active = :isActive 
                WHERE 
                    id = :id;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de atualização!'
                );
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':name' => $name,
                ':id' => $id,
                ':isActive' => $isActive
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de atualização!'
                );
            }

            $numberOfLinesAffected = $statement->rowCount();
            $wasTheRepositoryAffected = $numberOfLinesAffected > 0;

            return $wasTheRepositoryAffected;
        } catch (DatabaseStatementCreationFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Deletes an existing Platform in the repository.
     * @param int $id The id of the register to be deleted.
     * @return bool The success flag.
     * @throws PDOException Throwed in case of database error.
     */
    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'UPDATE
                    platform
                SET
                    is_active = :isActive
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException(
                    'Ocorreu um erro ao criar a declaração de atualização!'
                );
            }

            $wasTheUpdateSuccessfullyExecuted = $statement->execute([
                ':id' => $id,
                ':isActive' => $isActive
            ]);
            if ($wasTheUpdateSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de atualização!'
                );
            }

            return $wasTheUpdateSuccessfullyExecuted;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
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
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    platform 
                WHERE 
                    id = :id;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $result = $statement->fetch();
            if ($result === false) {
                return null;
            }

            $platform = new Platform();
            $platform->setId($result['id']);
            $platform->setName($result['name']);
            $platform->setIsActive($result['is_active']);

            return $platform;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
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
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    platform;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute();
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $result = $statement->fetchAll();
            if ($result === false) {
                return [];
            }

            $platforms = [];

            foreach ($result as $row) {
                $platform = new Platform();
                $platform->setId($row['id']);
                $platform->setName($row['name']);
                $platform->setIsActive($row['is_active']);
                $platforms[] = $platform;
            }

            return $platforms;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
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
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    platform 
                WHERE 
                    name = :name;'
            );
            if ($statement === false) {
                throw new DatabaseStatementCreationFailureException('Ocorreu um erro ao criar a declaração de busca!');
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':name' => $name
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new DatabaseStatementExecutionFailureException(
                    'Ocorreu um erro ao executar a declaração de busca!'
                );
            }

            $numberOfAffectedRows = $statement->rowCount();
            $hasDuplicatedNames = $numberOfAffectedRows > 0;
            return $hasDuplicatedNames;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
