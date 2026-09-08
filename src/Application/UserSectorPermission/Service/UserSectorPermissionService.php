<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service\Dto\UserSectorPermissionServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service\Dto\UserSectorPermissionServiceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Permission\Service\PermissionDomainService;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Sector\Service\SectorDomainService;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\Service\UserDomainService;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\Dto\UserSectorPermissionRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\Dto\UserSectorPermissionRepositoryInterfaceUpdateDto;
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
    private UserRepositoryInterface $userRepository;
    private SectorRepositoryInterface $sectorRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;
    private ClockInterface $clock;
    private LoggerInterface $logger;

    public function __construct(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        UserDomainService $userDomainService,
        SectorDomainService $sectorDomainService,
        PermissionDomainService $permissionDomainService,
        UserSectorPermissionDomainService $userSectorPermissionDomainService,
        UserRepositoryInterface $userRepository,
        SectorRepositoryInterface $sectorRepository,
        PermissionRepositoryInterface $permissionRepository,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        ClockInterface $clock,
        LoggerInterface $logger
    ) {
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->userDomainService = $userDomainService;
        $this->sectorDomainService = $sectorDomainService;
        $this->permissionDomainService = $permissionDomainService;
        $this->userSectorPermissionDomainService = $userSectorPermissionDomainService;
        $this->userRepository = $userRepository;
        $this->sectorRepository = $sectorRepository;
        $this->permissionRepository = $permissionRepository;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
        $this->clock = $clock;
        $this->logger = $logger;
    }

    public function insert(UserSectorPermissionServiceInsertDto $dto, string $token): UserSectorPermission
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::UserSectorPermission),
                PermissionValue::from(PermissionType::Create)
            );

            $this->userDomainService->ensureUserExists(
                $dto->userId
            );

            $this->sectorDomainService->ensureSectorExists(
                $dto->sectorId
            );

            $this->permissionDomainService->ensurePermissionExists(
                $dto->permissionId
            );

            $user = $this->userRepository->findById($dto->userId);
            $sector = $this->sectorRepository->findById($dto->sectorId);
            $permission = $this->permissionRepository->findById($dto->permissionId);

            $this->userSectorPermissionDomainService->assertSectorPermissionIsValid(
                $user,
                $sector,
                $permission
            );

            $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert(
                new UserSectorPermissionRepositoryInterfaceInsertDto(
                    $dto->userId,
                    $dto->sectorId,
                    $dto->permissionId
                )
            );

            $this->logger->notice("UserSectorPermission inserted succesfully!", [
                "id" => $insertedUserSectorPermission->getId()->getValue(),
                "username" => $insertedUserSectorPermission->getUser()->getUsername()->getValue(),
                "sector" => $insertedUserSectorPermission->getSector()->getName()->getValue(),
                "permission" => $insertedUserSectorPermission->getPermission()->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $insertedUserSectorPermission;
        } catch (\Throwable $e) {
            $this->logger->error("Error inserting UserSectorPermission!", [
                "exception" => $e->getMessage(),
                "id" => [
                    "user" => $dto->userId->getValue(),
                    "sector" => $dto->sectorId->getValue(),
                    "permission" => $dto->permissionId->getValue()
                ],
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function update(UserSectorPermissionServiceUpdateDto $dto, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::UserSectorPermission),
                PermissionValue::from(PermissionType::Update)
            );

            $this->userDomainService->ensureUserExists(
                $dto->userId
            );

            $this->sectorDomainService->ensureSectorExists(
                $dto->sectorId
            );

            $this->permissionDomainService->ensurePermissionExists(
                $dto->permissionId
            );

            $user = $this->userRepository->findById($dto->userId);
            $sector = $this->sectorRepository->findById($dto->sectorId);
            $permission = $this->permissionRepository->findById($dto->permissionId);

            $this->userSectorPermissionDomainService->assertSectorPermissionIsValid(
                $user,
                $sector,
                $permission
            );

            $wasUpdated = $this->userSectorPermissionRepository->update(
                new UserSectorPermissionRepositoryInterfaceUpdateDto(
                    $dto->id,
                    $dto->userId,
                    $dto->sectorId,
                    $dto->permissionId
                )
            );

            $this->logger->notice("UserSectorPermission updated succesfully!", [
                "id" => [
                    "user" => $dto->userId->getValue(),
                    "sector" => $dto->sectorId->getValue(),
                    "permission" => $dto->permissionId->getValue()
                ],
                "wasUpdated" => $wasUpdated,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasUpdated;
        } catch (\Throwable $e) {
            $this->logger->error("Error updating UserSectorPermission!", [
                "exception" => $e->getMessage(),
                "id" => [
                    "user" => $dto->userId->getValue(),
                    "sector" => $dto->sectorId->getValue(),
                    "permission" => $dto->permissionId->getValue()
                ],
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function delete(Id $id, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::UserSectorPermission),
                PermissionValue::from(PermissionType::Delete)
            );

            $this->userSectorPermissionDomainService->ensureUserSectorPermissionExists(
                $id
            );

            $wasDeleted = $this->userSectorPermissionRepository->delete($id);

            $this->logger->notice("UserSectorPermission deleted succesfully!", [
                "id" => $id->getValue(),
                "wasDeleted" => $wasDeleted,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $wasDeleted;
        } catch (\Throwable $e) {
            $this->logger->error("Error deleting UserSectorPermission!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?UserSectorPermission
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::UserSectorPermission),
                PermissionValue::from(PermissionType::List)
            );

            $fetchedUserSectorPermission = $this->userSectorPermissionRepository->findById(
                $id
            );

            if ($fetchedUserSectorPermission === null) {
                $this->logger->notice("UserSectorPermission not found!", [
                    "id" => $id->getValue(),
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
                return null;
            }

            $this->logger->notice("UserSectorPermission fetched succesfully!", [
                "id" => $id->getValue(),
                "username" => $fetchedUserSectorPermission?->getUser()->getUsername()->getValue(),
                "sector" => $fetchedUserSectorPermission?->getSector()->getName()->getValue(),
                "permission" => $fetchedUserSectorPermission?->getPermission()->getName()->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $fetchedUserSectorPermission;
        } catch (\Throwable $e) {
            $this->logger->error("Error fetching UserSectorPermission by id!", [
                "exception" => $e->getMessage(),
                "id" => $id->getValue(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }

    public function findAll(string $token): ?UserSectorPermissionCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorValue::from(SectorType::UserSectorPermission),
                PermissionValue::from(PermissionType::List)
            );

            $userSectorPermissions = $this->userSectorPermissionRepository->findAll();

            if ($userSectorPermissions === null) {
                $this->logger->notice("No UserSectorPermissions found!", [
                    "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
                ]);
                return null;
            }

            $this->logger->notice("UserSectorPermissions fetched succesfully!", [
                "count" => $userSectorPermissions->count(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return $userSectorPermissions;
        } catch (\Throwable $e) {
            $this->logger->error("Error fetching all UserSectorPermissions!", [
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }
}
