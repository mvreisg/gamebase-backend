<?php

declare(strict_types=1);

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
use Throwable;

class PlatformService
{
    private PlatformRepositoryInterface $repository;

    public function __construct(PlatformRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): Platform
    {
        $platform = new Platform();

        try {
            $platform->setName($name);
            $platform->setIsActive($isActive);

            $platform->validateName();

            $validatedName = $platform->getName();

            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome da plataforma a ser inserida já existe no repositório!'
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
            PDOException | 
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        $platform = new Platform();

        try {
            $platform->setId($id);
            $platform->setName($name);
            $platform->setIsActive($isActive);

            $platform->validateId();
            $platform->validateName();

            /*
            $validatedName = $platform->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome da plataforma a ser atualizada já existe no repositório!'
                );
            }
            */

            $wasTheUpdateSuccessful = $this->repository->update($platform);

            return $wasTheUpdateSuccessful;
        } catch (
            DatabaseDuplicatedEntryException |
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            PDOException | 
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        $platform = new Platform();

        try {
            $platform->setId($id);
            $platform->setIsActive($isActive);

            $platform->validateId();

            $wasSuccessful = $this->repository->setIsActive($id, $isActive);

            return $wasSuccessful;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException | 
            Throwable $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): Platform|null
    {
        $platform = new Platform();

        try {
            $platform->setId($id);

            $platform->validateId();

            $platform = $this->repository->findById($id);

            return $platform;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException | 
            Throwable $e
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
            PDOException | 
            Throwable $e
        ) {
            throw $e;
        }
    }
}
