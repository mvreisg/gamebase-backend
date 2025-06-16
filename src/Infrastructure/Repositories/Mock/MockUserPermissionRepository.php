<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantRegisterException;

class MockUserPermissionRepository implements UserPermissionRepositoryInterface
{
    private array $data;
    private int $id;
    private UserRepositoryInterface $userRepository;
    private PermissionRepositoryInterface $permissionRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PermissionRepositoryInterface $permissionRepository
    ) {
        $this->data = [];
        $this->id = 0;
        $this->userRepository = $userRepository;
        $this->permissionRepository = $permissionRepository;
    }

    public function insert(UserPermission $userPermission): UserPermission
    {
        try{
            $userId = $userPermission->getUserId();
            $user = $this->userRepository->findById($userId);
            if ($user === null){
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o id ' . $userId . ' não existe!'
                );
            }

            $permissionId = $userPermission->getPermissionId();
            $permission = $this->permissionRepository->findById($permissionId);
            if ($permission === null){
                throw new DatabaseUnexistantRegisterException(
                    'O registro com o id ' . $permissionId . ' não existe!'
                );                
            }

            $this->id++;
            $id = $this->id;

            $userPermission->setId($id);
            $this->data[] = $userPermission;
            $newUserPermission = new UserPermission(
                $id,
                $userId,
                $permissionId
            );
            return $newUserPermission;
        } catch (DatabaseUnexistantRegisterException $e){
            throw $e;
        }        
    }

    public function update(UserPermission $userPermission): bool
    {
        $idToUpdate = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $userPermission->getId()) {
                $idToUpdate = $key;
                break;
            }
        }

        if ($idToUpdate === null) {            
            return false;
        }

        $userPermissionToBeModified = $this->data[$idToUpdate];

        $hasDifferentUserId = 
            $userPermissionToBeModified->getUserId() !== $userPermission->getUserId();

        $hasDifferentPermissionId = 
            $userPermissionToBeModified->getPermissionId() !== $userPermission->getPermissionId();            

        $userPermissionToBeModified->setUserId(
            $userPermission->getUserId()
        );
        $userPermissionToBeModified->setPermissionId(
            $userPermission->getPermissionId()
        );

        $this->data[$idToUpdate] = $userPermissionToBeModified;

        $wasModified = $hasDifferentUserId || $hasDifferentPermissionId;
        return $wasModified;
    }

    public function delete(UserPermission $userPermission): bool
    {
        $idToDelete = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $userPermission->getId()) {
                $idToDelete = $key;
                break;
            }
        }

        if ($idToDelete > -1) {
            unset($this->data[$idToDelete]);
            return true;
        } else {
            return false;
        }
    }

    /*
    public function setIsActive(int $id, bool $isActive): bool
    {
        $idToSet = null;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                $idToSet = $key;
            }
        }

        if ($idToSet === null) {
            return false;
        }

        $findedUserPermission = $this->data[$idToSet];

        $changedSomething = $findedUserPermission->getIsActive() !== $isActive;

        if ($changedSomething) {
            $this->data[$idToSet]->setIsActive($isActive);
            return true;
        }        
        
        return false;
    }
    */

    public function findById(int $id): UserPermission|null
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return $this->data;
    }
}
