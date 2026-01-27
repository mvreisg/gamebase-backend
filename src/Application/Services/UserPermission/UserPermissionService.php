<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\UserPermission;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;

class UserPermissionService
{
    private UserRepositoryInterface $userRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private UserPermissionRepositoryInterface $userPermissionRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PermissionRepositoryInterface $permissionRepository,
        UserPermissionRepositoryInterface $userPermissionRepository
    ) {
        $this->userRepository = $userRepository;
        $this->permissionRepository = $permissionRepository;
        $this->userPermissionRepository = $userPermissionRepository;
    }

    public function insert(UserPermission $new): UserPermission
    {
        try {
            $this->userRepository->checkIfExists(
                Id::make($new->getUserIdValue())
            );

            $this->permissionRepository->checkIfExists(
                Id::make($new->getPermissionIdValue())
            );

            $insertedUserPermission = $this->userPermissionRepository->insert($new);

            return $insertedUserPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(UserPermission $existant): bool
    {
        try {
            $this->userPermissionRepository->checkIfExists(
                Id::make($existant->getIdValue())
            );

            $this->userRepository->checkIfExists(
                Id::make($existant->getUserIdValue())
            );

            $this->permissionRepository->checkIfExists(
                Id::make($existant->getPermissionIdValue())
            );

            $wasUpdated = $this->userPermissionRepository->update($existant);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(Id $id): bool
    {
        try {
            $this->userPermissionRepository->checkIfExists($id);

            $wasDeleted = $this->userPermissionRepository->delete($id);

            return $wasDeleted;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id): UserPermission
    {
        try {
            $fetchedUserPermission = $this->userPermissionRepository->findById(
                $id
            );

            return $fetchedUserPermission;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): UserPermissionCollection
    {
        try {
            return $this->userPermissionRepository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
