<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\SectorPermission;

use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceInvalidPermissionIdException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceInvalidSectorIdException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceUnexistantPermissionException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceUnexistantSectorException;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermission\Exceptions\SectorPermissionServiceUnexistantSectorPermissionException;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermission\Exceptions\SectorPermissionInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermission\Exceptions\SectorPermissionInvalidPermissionIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermission\Exceptions\SectorPermissionInvalidSectorIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermission\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorPermissionInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;

class SectorPermissionService
{
    private SectorRepositoryInterface $sectorRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private SectorPermissionInterface $sectorPermissionRepository;

    public function __construct(
        SectorRepositoryInterface $sectorRepository,
        PermissionRepositoryInterface $permissionRepository,
        SectorPermissionInterface $sectorPermissionRepository
    ) {
        $this->sectorRepository = $sectorRepository;
        $this->permissionRepository = $permissionRepository;
        $this->sectorPermissionRepository = $sectorPermissionRepository;
    }

    public function insert(int $sectorId, int $permissionId): SectorPermission
    {
        try {
            $sectorPermission = new SectorPermission(
                null,
                $sectorId,
                $permissionId
            );

            $sectorPermission->validateSectorId();
            $sectorPermission->validatePermissionId();

            try {
                $validatedSectorId = $sectorPermission->getSectorId();

                $this->sectorRepository->checkIfExists($validatedSectorId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new SectorPermissionServiceUnexistantSectorException(
                    "Sector permission service error: Sector repository: {$e->getMessage()}",
                    $e
                );
            }

            try {
                $validatedPermissionId = $sectorPermission->getPermissionId();

                $this->permissionRepository->checkIfExists($validatedPermissionId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new SectorPermissionServiceUnexistantPermissionException(
                    "Sector permission service error: Permission repository: {$e->getMessage()}",
                    $e
                );
            }

            $insertedSectorPermission = $this->sectorPermissionRepository->insert($sectorPermission);

            return $insertedSectorPermission;
        } catch (
            SectorPermissionServiceUnexistantSectorException |
            SectorPermissionServiceUnexistantPermissionException
            $e
        ) {
            throw $e;
        } catch (SectorPermissionInvalidSectorIdException $e) {
            throw new SectorPermissionServiceInvalidSectorIdException(
                "Permission permission service error: {$e->getMessage()}",
                $e
            );
        } catch (SectorPermissionInvalidPermissionIdException $e) {
            throw new SectorPermissionServiceInvalidPermissionIdException(
                "Permission permission service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function update(int $id, int $sectorId, int $permissionId): bool
    {
        try {
            $sectorPermission = new SectorPermission(
                $id,
                $sectorId,
                $permissionId
            );

            $sectorPermission->validateId();
            $sectorPermission->validateSectorId();
            $sectorPermission->validatePermissionId();

            $validatedId = $sectorPermission->getId();

            $this->sectorPermissionRepository->checkIfExists($validatedId);

            try {
                $validatedSectorId = $sectorPermission->getSectorId();

                $this->sectorRepository->checkIfExists($validatedSectorId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new SectorPermissionServiceUnexistantSectorException(
                    "Sector permission service error: Sector repository: {$e->getMessage()}",
                    $e
                );
            }

            try {
                $validatedPermissionId = $sectorPermission->getPermissionId();

                $this->permissionRepository->checkIfExists($validatedPermissionId);
            } catch (RepositoryUnexistantRegisterException $e) {
                throw new SectorPermissionServiceUnexistantPermissionException(
                    "Sector permission service error: Permission repository: {$e->getMessage()}",
                    $e
                );
            }

            $wasUpdated = $this->sectorPermissionRepository->update($sectorPermission);

            return $wasUpdated;
        } catch (
            SectorPermissionServiceUnexistantSectorException |
            SectorPermissionServiceUnexistantPermissionException
        $e) {
            throw $e;
        } catch (SectorPermissionInvalidIdException $e) {
            throw new SectorPermissionServiceInvalidIdException(
                "Sector permission service error: {$e->getMessage()}",
                $e
            );
        } catch (SectorPermissionInvalidSectorIdException $e) {
            throw new SectorPermissionServiceInvalidSectorIdException(
                "Sector permission service error: {$e->getMessage()}",
                $e
            );
        } catch (SectorPermissionInvalidPermissionIdException $e) {
            throw new SectorPermissionServiceInvalidPermissionIdException(
                "Sector permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new SectorPermissionServiceUnexistantSectorPermissionException(
                "Sector permission service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sectorPermission = new SectorPermission(
                $id
            );

            $sectorPermission->validateId();

            $validatedId = $sectorPermission->getId();

            $this->sectorPermissionRepository->checkIfExists($validatedId);

            $wasDeleted = $this->sectorPermissionRepository->delete($sectorPermission);

            return $wasDeleted;
        } catch (SectorPermissionInvalidIdException $e) {
            throw new SectorPermissionServiceInvalidIdException(
                "Sector permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new SectorPermissionServiceUnexistantSectorPermissionException(
                "Sector permission service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
        $e) {
            throw $e;
        }
    }

    public function findById(int $id): SectorPermission
    {
        try {
            $sectorPermission = new SectorPermission(
                $id
            );

            $sectorPermission->validateId();

            $validatedId = $sectorPermission->getId();

            $fetchedSectorPermission = $this->sectorPermissionRepository->findById(
                $validatedId
            );

            return $fetchedSectorPermission;
        } catch (SectorPermissionInvalidIdException $e) {
            throw new SectorPermissionServiceInvalidIdException(
                "Sector permission service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new SectorPermissionServiceUnexistantSectorPermissionException(
                "Sector permission service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable

        $e) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            return $this->sectorPermissionRepository->findAll();
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable

        $e) {
            throw $e;
        }
    }
}
