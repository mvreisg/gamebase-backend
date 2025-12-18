<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Sector;

use Mvreisg\GamebaseBackend\Application\Services\Sector\Exceptions\SectorServiceDuplicatedNameException;
use Mvreisg\GamebaseBackend\Application\Services\Sector\Exceptions\SectorServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\Sector\Exceptions\SectorServiceInvalidNameException;
use Mvreisg\GamebaseBackend\Application\Services\Sector\Exceptions\SectorServiceUnexistantSectorException;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector\Exceptions\SectorInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector\Exceptions\SectorInvalidNameException;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector\Sector;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;

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
            $sector = new Sector(
                null,
                $name,
                $isActive
            );

            $sector->validateName();

            $validatedName = $sector->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $insertedSector = $this->repository->insert($sector);

            return $insertedSector;
        } catch (SectorInvalidNameException $e) {
            throw new SectorServiceInvalidNameException(
                "Sector service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedNameException $e) {
            throw new SectorServiceDuplicatedNameException(
                "Sector service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        try {
            $sector = new Sector(
                $id,
                $name,
                $isActive
            );

            $sector->validateId();

            $validatedId = $sector->getId();
            $this->repository->checkIfExists($validatedId);

            $sector->validateName();

            $validatedName = $sector->getName();
            $this->repository->checkDuplicatedNames($validatedName);

            $wasUpdated = $this->repository->update($sector);
            return $wasUpdated;
        } catch (SectorInvalidIdException $e) {
            throw new SectorServiceInvalidIdException(
                "Sector service error: {$e->getMessage()}",
                $e
            );
        } catch (SectorInvalidNameException $e) {
            throw new SectorServiceInvalidNameException(
                "Sector service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedNameException $e) {
            throw new SectorServiceDuplicatedNameException(
                "Sector service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new SectorServiceUnexistantSectorException(
                "Sector service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $sector = new Sector(
                $id,
                null,
                $isActive
            );

            $sector->validateId();

            $validatedId = $sector->getId();
            $this->repository->checkIfExists($validatedId);

            $validatedIsActive = $sector->getIsActive();

            $wasUpdated = $this->repository->setIsActive(
                $validatedId,
                $validatedIsActive
            );

            return $wasUpdated;
        } catch (SectorInvalidIdException $e) {
            throw new SectorServiceInvalidIdException(
                "Sector service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new SectorServiceUnexistantSectorException(
                "Sector service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): Sector
    {
        try {
            $sector = new Sector(
                $id
            );

            $sector->validateId();

            $validatedId = $sector->getId();

            $fetchedSector = $this->repository->findById($validatedId);

            return $fetchedSector;
        } catch (SectorInvalidIdException $e) {
            throw new SectorServiceInvalidIdException(
                "Sector service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new SectorServiceUnexistantSectorException(
                "Sector service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            return $this->repository->findAll();
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }
}
