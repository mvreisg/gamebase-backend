<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authorization\UseCase;

use Mvreisg\GamebaseBackend\Domain\Authentication\Service\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\User\Service\UserDomainService;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;
use Psr\Log\LoggerInterface;

class CheckAuthorizationUseCase
{
    private UserDomainService $userDomainService;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;
    private AuthenticationService $authenticationService;
    private AuthorizationDomainService $authorizationDomainService;
    private ClockInterface $clock;
    private LoggerInterface $logger;

    public function __construct(
        UserDomainService $userDomainService,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        AuthenticationService $authenticationService,
        AuthorizationDomainService $authorizationDomainService,
        ClockInterface $clock,
        LoggerInterface $logger
    ) {
        $this->userDomainService = $userDomainService;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
        $this->authenticationService = $authenticationService;
        $this->authorizationDomainService = $authorizationDomainService;
        $this->clock = $clock;
        $this->logger = $logger;
    }

    public function execute(string $token, SectorType $sectorType, PermissionType $permissionType): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $userId = $decodedToken->getAuthenticationData()->getUserId();

            $this->userDomainService->ensureUserExists(
                $userId
            );

            $userSectorPermissions = $this->userSectorPermissionRepository->findAllByUserId(
                $userId
            );

            if ($userSectorPermissions === null) {
                $userSectorPermissions = new UserSectorPermissionCollection();
            }

            $this->authorizationDomainService->ensureHasPermission(
                $userSectorPermissions,
                $sectorType,
                $permissionType
            );

            $this->logger->error("Successfully authorized", [
                "sector" => $sectorType->value,
                "permission" => $permissionType->value,
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error("Authorization check failed", [
                "sector" => $sectorType->value,
                "permission" => $permissionType->value,
                "exception" => $e->getMessage(),
                "timestamp" => $this->clock->now()->format(\DateTimeInterface::ATOM)
            ]);
            throw $e;
        }
    }
}
