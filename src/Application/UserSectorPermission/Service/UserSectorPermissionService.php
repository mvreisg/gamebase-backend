<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Permission\Service\PermissionDomainService;
use Mvreisg\GamebaseBackend\Domain\Sector\Service\SectorDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Service\UserDomainService;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Service\UserSectorPermissionDomainService;
use Psr\Log\LoggerInterface;

class UserSectorPermissionService
{
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private UserDomainService $userDomainService;
    private SectorDomainService $sectorDomainService;
    private PermissionDomainService $permissionDomainService;
    private UserSectorPermissionDomainService $userSectorPermissionDomainService;
    private UserSectorPermissionRepositoryInterface $repository;
    private LoggerInterface $logger;

    public function __construct(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        UserDomainService $userDomainService,
        SectorDomainService $sectorDomainService,
        PermissionDomainService $permissionDomainService,
        UserSectorPermissionDomainService $userSectorPermissionDomainService,
        UserSectorPermissionRepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->userDomainService = $userDomainService;
        $this->sectorDomainService = $sectorDomainService;
        $this->permissionDomainService = $permissionDomainService;
        $this->userSectorPermissionDomainService = $userSectorPermissionDomainService;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function insert(UserSectorPermission $new, string $token): UserSectorPermission
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::UserSectorPermission,
                PermissionType::Create
            );

            $this->userDomainService->ensureUserExists(
                $new->getUser()->getId()
            );

            $this->sectorDomainService->ensureSectorExists(
                $new->getSector()->getId()
            );

            $this->permissionDomainService->ensurePermissionExists(
                $new->getPermission()->getId()
            );

            $this->userSectorPermissionDomainService->ensureUserSectorPermissionExists(
                $new->getId()
            );

            $this->userSectorPermissionDomainService->assertSectorPermissionIsValid(
                $new
            );

            $insertedUserSectorPermission = $this->repository->insert($new);

            return $insertedUserSectorPermission;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting UserSectorPermission", [
                "exception" => $e,
                "userSectorPermission" => $new,
            ]);
            throw $e;
        }
    }

    public function update(UserSectorPermission $existant, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::UserSectorPermission,
                PermissionType::Update
            );

            $this->userDomainService->ensureUserExists(
                $existant->getUser()->getId()
            );

            $this->sectorDomainService->ensureSectorExists(
                $existant->getSector()->getId()
            );

            $this->permissionDomainService->ensurePermissionExists(
                $existant->getPermission()->getId()
            );

            $this->userSectorPermissionDomainService->ensureUserSectorPermissionExists(
                $existant->getId()
            );

            $this->userSectorPermissionDomainService->assertSectorPermissionIsValid(
                $existant
            );

            $wasUpdated = $this->repository->update($existant);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating UserSectorPermission", [
                "exception" => $e,
                "userSectorPermission" => $existant,
            ]);
            throw $e;
        }
    }

    public function delete(Id $id, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::UserSectorPermission,
                PermissionType::Delete
            );

            $this->userSectorPermissionDomainService->ensureUserSectorPermissionExists(
                $id
            );

            $wasDeleted = $this->repository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            $this->logger->error("Error deleting UserSectorPermission", [
                "exception" => $e,
                "userSectorPermissionId" => $id,
            ]);
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?UserSectorPermission
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::UserSectorPermission,
                PermissionType::List
            );

            $fetchedUserPermission = $this->repository->findById(
                $id
            );

            return $fetchedUserPermission;
        } catch (\Throwable $e) {
            $this->logger->error("Error fetching UserSectorPermission by ID", [
                "exception" => $e,
                "userSectorPermissionId" => $id,
            ]);
            throw $e;
        }
    }

    public function findAll(string $token): ?UserSectorPermissionCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::UserSectorPermission,
                PermissionType::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            $this->logger->error("Error fetching all UserSectorPermissions", [
                "exception" => $e,
            ]);
            throw $e;
        }
    }
}
