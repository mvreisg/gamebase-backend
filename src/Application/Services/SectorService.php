<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use PDOException;
use Throwable;

class SectorService
{
    private SectorRepositoryInterface $repository;

    public function __construct(SectorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): Sector
    {
        try {
            $sector = new Sector(PHP_INT_MAX, $name, $isActive);
            $sector->validateId();
            $sector->validateName();
            $validatedName = $sector->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome do setor a ser inserido já existe no repositório!'
                );
            }

            $sectorCopy = $this->repository->insert($sector);
            return $sectorCopy;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            EntityInvalidValueException |
            PDOException | 
            Throwable $e
        ) {
                throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        try {
            $sector = new Sector($id, $name, $isActive);
            $sector->validateId();
            $sector->validateName();
            /*
            $validatedName = $sector->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome do setor a ser atualizado já existe no repositório!'
                );
            }
            */

            $wasTheUpdateSuccessful = $this->repository->update($sector);
            return $wasTheUpdateSuccessful;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseDuplicatedEntryException |
            EntityInvalidValueException |
            PDOException | 
            Throwable $e
        ) {
                throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $sector = new Sector($id, '', $isActive);
            $sector->validateId();

            $wasSomethingChanged = $this->repository->setIsActive($id, $isActive);
            return $wasSomethingChanged;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            EntityInvalidValueException |
            PDOException | 
            Throwable $e
        ) {
                throw $e;
        }
    }

    public function findById(int $id): Sector
    {
        try {
            $sector = new Sector($id);
            $sector->validateId();

            $fetchedPermission = $this->repository->findById($id);
            return $fetchedPermission;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            EntityInvalidValueException |
            PDOException | 
            Throwable $e
        ) {
                throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $fetchedPermissions = $this->repository->findAll();
            return $fetchedPermissions;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException | 
            Throwable $e
        ) {
                throw $e;
        }
    }
}
