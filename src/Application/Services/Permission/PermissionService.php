<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Permission;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Entities\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;

class PermissionService
{
    private PermissionRepositoryInterface $repository;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        PermissionRepositoryInterface $repository,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
    ) {
        $this->repository = $repository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function insert(Permission $permission, EncodedAuthenticationToken $token): Permission
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Permission,
                PermissionTypes::Create
            );

            $this->repository->checkDuplicatedNames(
                $permission->getName()
            );

            $insertedPermission = $this->repository->insert($permission);

            return $insertedPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Permission $permission, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Permission,
                PermissionTypes::Update
            );

            $this->repository->checkIfExists(
                $permission->getId()
            );

            $this->repository->checkDuplicatedNames(
                $permission->getName()
            );

            $wasUpdated = $this->repository->update($permission);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Permission,
                PermissionTypes::Activate
            );

            $this->repository->checkIfExists($id);

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, EncodedAuthenticationToken $token): Permission
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Permission,
                PermissionTypes::List
            );

            $fetchedPermission = $this->repository->findById($id);

            return $fetchedPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(EncodedAuthenticationToken $token): PermissionCollection
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Permission,
                PermissionTypes::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
