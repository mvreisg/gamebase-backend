<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB;

use PDO;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermissionEntity;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorPermissionEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB\MariaDBTransactionCreationFailureException;

class MariaDBSectorPermissionEntityRepository implements SectorPermissionEntityRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(SectorPermissionEntity $sectorPermissionEntity): SectorPermissionEntity
    {
        try {
            $wasTheTransactionSuccessfullyCreated = $this->pdo->beginTransaction();
            if ($wasTheTransactionSuccessfullyCreated === false) {
                throw new MariaDBTransactionCreationFailureException();
            }

            $sectorId = $sectorPermissionEntity->getSectorId();
            $permissionId = $sectorPermissionEntity->getPermissionId();

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO 
                    sector_permission 
                        (sector_id, permission_id) 
                VALUES 
                    (:sectorId, :permissionId);'
            );
            if ($insertStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheInsertStatementSuccessfullyExecuted = $insertStatement->execute([                
                ':sectorId' => $sectorId,
                ':permissionId' => $permissionId
            ]);
            if ($wasTheInsertStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $lastInsertedId = $this->pdo->lastInsertId();
            $lastInsertedId = intval($lastInsertedId);

            $selectStatement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    sector_permission 
                WHERE 
                    id = :id;'
            );
            if ($selectStatement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheSelectStatementSuccessfullyExecuted = $selectStatement->execute([
                ':id' => $lastInsertedId
            ]);
            if ($wasTheSelectStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $selectStatement->fetch();
            if ($fetchResult === false) {
                throw new MariaDBFetchFailureException();
            }

            $this->pdo->commit();

            $sectorPermissionEntity = new SectorPermissionEntity(
                $fetchResult['id'],
                $fetchResult['sector_id'],
                $fetchResult['permission_id']
            );

            return $sectorPermissionEntity;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(SectorPermissionEntity $sectorPermissionEntity): bool
    {
        try {
            $id = $sectorPermissionEntity->getId();            
            $sectorId = $sectorPermissionEntity->getSectorId();
            $permissionId = $sectorPermissionEntity->getPermissionId();            

            $statement = $this->pdo->prepare(
                'UPDATE 
                    sector_permission 
                SET 
                    sector_id = :sectorId, 
                    permission_id = :permissionId 
                WHERE 
                    id = :id;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementExecutionSuccessful = $statement->execute([
                ':id' => $id,
                ':sectorId' => $sectorId,
                ':permissionId' => $permissionId
            ]);
            if ($wasTheStatementExecutionSuccessful === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $numberOfRowsAffected = $statement->rowCount();
            $wasTheRepositoryAffected = $numberOfRowsAffected > 0;

            return $wasTheRepositoryAffected;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(SectorPermissionEntity $sectorPermissionEntity): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'DELETE FROM
                    sector_permission
                WHERE
                    id = :id;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $id = $sectorPermissionEntity->getId();

            $wasTheDeleteStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);
            if ($wasTheDeleteStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $numberOfRowsAffected = $statement->rowCount();
            $wasTheDeleteSuccessful = $numberOfRowsAffected > 0;

            return $wasTheDeleteSuccessful;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(int $id): SectorPermissionEntity|null
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    sector_permission 
                WHERE 
                    id = :id;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute([
                ':id' => $id
            ]);
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetch();
            if ($fetchResult === false) {
                return null;
            }

            $sectorPermissionEntity = new SectorPermissionEntity(
                $fetchResult['id'],
                $fetchResult['sector_id'],
                $fetchResult['permission_id']
            );

            return $sectorPermissionEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT 
                    * 
                FROM 
                    sector_permission;'
            );
            if ($statement === false) {
                throw new MariaDBStatementCreationFailureException();
            }

            $wasTheStatementSuccessfullyExecuted = $statement->execute();
            if ($wasTheStatementSuccessfullyExecuted === false) {
                throw new MariaDBStatementExecutionFailureException();
            }

            $fetchResult = $statement->fetchAll();
            if ($fetchResult === false) {
                return [];
            }

            $userPermissionEntities = [];
            foreach ($fetchResult as $row) {
                $sectorPermissionEntity = new SectorPermissionEntity(
                    $row['id'],
                    $row['sector_id'],
                    $row['permission_id']
                );

                $userPermissionEntities[] = $sectorPermissionEntity;
            }

            return $userPermissionEntities;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
