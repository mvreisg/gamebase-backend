<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authorization\UseCase;

use Mvreisg\GamebaseBackend\Application\Authentication\Service\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
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
    private LoggerInterface $logger;

    public function __construct(
        UserDomainService $userDomainService,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        AuthenticationService $authenticationService,
        AuthorizationDomainService $authorizationDomainService,
        LoggerInterface $logger
    ) {
        $this->userDomainService = $userDomainService;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
        $this->authenticationService = $authenticationService;
        $this->authorizationDomainService = $authorizationDomainService;
        $this->logger = $logger;
    }

    public function execute(string $token, SectorType $sectorType, PermissionType $permissionType): void
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
        } catch (\Throwable $e) {
            $this->logger->error("Authorization check failed", [
                "sectorType" => $sectorType->value,
                "permissionType" => $permissionType->value,
                "error" => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
