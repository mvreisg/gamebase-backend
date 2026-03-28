<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\UserSectorPermission;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;

class UserSectorPermissionService
{
    private UserRepositoryInterface $userRepository;
    private SectorRepositoryInterface $sectorRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private UserSectorPermissionRepositoryInterface $userSectorPermissionRepository;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SectorRepositoryInterface $sectorRepository,
        PermissionRepositoryInterface $permissionRepository,
        UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->userRepository = $userRepository;
        $this->sectorRepository = $sectorRepository;
        $this->permissionRepository = $permissionRepository;
        $this->userSectorPermissionRepository = $userSectorPermissionRepository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function insert(UserSectorPermission $new, EncodedAuthenticationToken $token): UserSectorPermission
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::Create
            );

            $this->userRepository->checkIfExists(
                $new->getUserId()
            );

            $this->sectorRepository->checkIfExists(
                $new->getSectorId()
            );

            $this->permissionRepository->checkIfExists(
                $new->getPermissionId()
            );

            $insertedUserSectorPermission = $this->userSectorPermissionRepository->insert($new);

            return $insertedUserSectorPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(UserSectorPermission $existant, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::Update
            );

            $this->userSectorPermissionRepository->checkIfExists(
                $existant->getId()
            );

            $this->userRepository->checkIfExists(
                $existant->getUserId()
            );

            $this->sectorRepository->checkIfExists(
                $existant->getSectorId()
            );

            $this->permissionRepository->checkIfExists(
                $existant->getPermissionId()
            );

            $wasUpdated = $this->userSectorPermissionRepository->update($existant);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::Delete
            );

            $this->userSectorPermissionRepository->checkIfExists($id);

            $wasDeleted = $this->userSectorPermissionRepository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, EncodedAuthenticationToken $token): UserSectorPermission
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::List
            );

            $fetchedUserPermission = $this->userSectorPermissionRepository->findById(
                $id
            );

            return $fetchedUserPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(EncodedAuthenticationToken $token): UserSectorPermissionCollection
    {
        try {
            $decodedToken = $this->authenticationService->decode(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::List
            );

            return $this->userSectorPermissionRepository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
