<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Platform;

use Mvreisg\GamebaseBackend\Application\Services\Platform\Exceptions\PlatformServiceDuplicatedNameException;
use Mvreisg\GamebaseBackend\Application\Services\Platform\Exceptions\PlatformServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\Platform\Exceptions\PlatformServiceInvalidNameException;
use Mvreisg\GamebaseBackend\Application\Services\Platform\Exceptions\PlatformServiceUnexistantPlatformException;
use Mvreisg\GamebaseBackend\Domain\Entities\Platform\Exceptions\PlatformInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\Platform\Exceptions\PlatformInvalidNameException;
use Mvreisg\GamebaseBackend\Domain\Entities\Platform\Platform;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;

class PlatformService
{
    private PlatformRepositoryInterface $repository;

    public function __construct(PlatformRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): Platform
    {
        try {
            $platform = new Platform(
                null,
                $name,
                $isActive
            );

            $platform->validateName();

            $validatedName = $platform->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $insertedPlatform = $this->repository->insert($platform);

            return $insertedPlatform;
        } catch (PlatformInvalidNameException $e) {
            throw new PlatformServiceInvalidNameException(
                "Platform service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedNameException $e) {
            throw new PlatformServiceDuplicatedNameException(
                "Platform service error: {$e->getMessage()}",
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
            $platform = new Platform(
                $id,
                $name,
                $isActive
            );

            $platform->validateId();

            $validatedId = $platform->getId();
            $this->repository->checkIfExists($validatedId);

            $platform->validateName();

            $validatedName = $platform->getName();
            $this->repository->checkDuplicatedNames($validatedName);

            $wasUpdated = $this->repository->update($platform);
            return $wasUpdated;
        } catch (PlatformInvalidIdException $e) {
            throw new PlatformServiceInvalidIdException(
                "Platform service error: {$e->getMessage()}",
                $e
            );
        } catch (PlatformInvalidNameException $e) {
            throw new PlatformServiceInvalidNameException(
                "Platform service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedNameException $e) {
            throw new PlatformServiceDuplicatedNameException(
                "Platform service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new PlatformServiceUnexistantPlatformException(
                "Platform service error: {$e->getMessage()}",
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
            $platform = new Platform(
                $id,
                null,
                $isActive
            );

            $platform->validateId();

            $validatedId = $platform->getId();
            $this->repository->checkIfExists($validatedId);

            $validatedIsActive = $platform->getIsActive();

            $wasUpdated = $this->repository->setIsActive(
                $validatedId,
                $validatedIsActive
            );

            return $wasUpdated;
        } catch (PlatformInvalidIdException $e) {
            throw new PlatformServiceInvalidIdException(
                "Platform service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new PlatformServiceUnexistantPlatformException(
                "Platform service error: {$e->getMessage()}",
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

    public function findById(int $id): Platform
    {
        try {
            $platform = new Platform(
                $id
            );

            $platform->validateId();

            $validatedId = $platform->getId();

            $fetchedPlatform = $this->repository->findById($validatedId);

            return $fetchedPlatform;
        } catch (PlatformInvalidIdException $e) {
            throw new PlatformServiceInvalidIdException(
                "Platform service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new PlatformServiceUnexistantPlatformException(
                "Platform service error: {$e->getMessage()}",
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
