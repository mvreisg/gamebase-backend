<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authorization\UseCase;

use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\User\Service\UserDomainService;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;

class CheckAuthorizationUseCase
{
    private UserDomainService $userDomainService;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;
    private AuthenticationService $authenticationService;
    private AuthorizationDomainService $authorizationDomainService;

    public function __construct(
        UserDomainService $userDomainService,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        AuthenticationService $authenticationService,
        AuthorizationDomainService $authorizationDomainService
    ) {
        $this->userDomainService = $userDomainService;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
        $this->authenticationService = $authenticationService;
        $this->authorizationDomainService = $authorizationDomainService;
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
            throw $e;
        }
    }
}
