<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use PDOException;

class PlatformService
{
    private PlatformRepositoryInterface $repository;

    public function __construct(PlatformRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(mixed $name, mixed $isActive): Platform
    {
        $platform = new Platform();

        try {
            $platform->validateName($name);
            $platform->validateIsActive($isActive);
            $platform->setName($name);
            $platform->setIsActive($isActive);
            $validatedName = $platform->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome da plataforma a ser inserida já existe no banco de dados!'
                );
            }
            $platform = $this->repository->insert($platform);
            return $platform;
        } catch (
            DatabaseDuplicatedEntryException |
            EntityInvalidValueException |
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function update(mixed $id, mixed $name, mixed $isActive): bool
    {
        $platform = new Platform();

        try {
            $platform->validateId($id);
            $platform->validateName($name);
            $platform->validateIsActive($isActive);
            $platform->setId($id);
            $platform->setName($name);
            $platform->setIsActive($isActive);
            $validatedName = $platform->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome da plataforma a ser atualizada já existe no repositório!'
                );
            }
            $wasTheUpdateSuccessful = $this->repository->update($platform);
            return $wasTheUpdateSuccessful;
        } catch (
            DatabaseDuplicatedEntryException |
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(mixed $id, mixed $isActive): bool
    {
        $platform = new Platform();
        try {
            $platform->validateId($id);
            $platform->validateIsActive($isActive);
            $platform->setId($id);
            $platform->setIsActive($isActive);
            $wasSuccessful = $this->repository->setIsActive($id, $isActive);
            return $wasSuccessful;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findById(mixed $id): Platform|null
    {
        $platform = new Platform();

        try {
            $platform->validateId($id);
            $platform->setId($id);
            $platform = $this->repository->findById($id);
            return $platform;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $platforms = $this->repository->findAll();
            return $platforms;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
