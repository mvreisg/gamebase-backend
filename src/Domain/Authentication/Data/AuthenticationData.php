<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Data;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Permission;
use Mvreisg\GamebaseBackend\Domain\Data\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Sector;
use Mvreisg\GamebaseBackend\Domain\Data\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Username;

class AuthenticationData
{
    private Id $userId;
    private Username $username;
    private PermissionCollection $permissionCollection;
    private SectorCollection $sectorCollection;
    private SectorPermissionCollection $sectorPermissionCollection;

    public function __construct(
        Id $userId,
        Username $username,
        PermissionCollection $permissionCollection,
        SectorCollection $sectorCollection,
        SectorPermissionCollection $sectorPermissionCollection
    ) {
        $this->userId = $userId;
        $this->username = $username;
        $this->permissionCollection = $permissionCollection;
        $this->sectorCollection = $sectorCollection;
        $this->sectorPermissionCollection = $sectorPermissionCollection;
    }

    public static function toObject(\stdClass $data): self
    {
        $permissions = [];
        foreach ($data->permissions as $permission) {
            $value = new Permission(
                Name::make($permission->name),
                $permission->isActive
            );
            $value->setId(Id::make($permission->id));
            $permissions[] = $value;
        }
        $sectors = [];
        foreach ($data->sectors as $sector) {
            $value = new Sector(
                Name::make($sector->name),
                $sector->isActive
            );
            $value->setId(Id::make($sector->id));
            $sectors[] = $value;
        }
        $sectorPermissions = [];
        foreach ($data->sectorPermissions as $sectorPermission) {
            $value = new SectorPermission(
                Id::make($sectorPermission->sectorId),
                Id::make($sectorPermission->permissionId)
            );
            $value->setId(Id::make($sectorPermission->id));
            $sectorPermissions[] = $value;
        }
        return new self(
            Id::make($data->userId),
            Username::make($data->username),
            new PermissionCollection($permissions),
            new SectorCollection($sectors),
            new SectorPermissionCollection($sectorPermissions)
        );
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getPermissionCollection(): PermissionCollection
    {
        return $this->permissionCollection;
    }

    public function getSectorCollection(): SectorCollection
    {
        return $this->sectorCollection;
    }

    public function getSectorPermissionCollection(): SectorPermissionCollection
    {
        return $this->sectorPermissionCollection;
    }

    public function toArray(): array
    {
        $permissions = [];
        foreach ($this->permissionCollection->fetchAll() as $permission) {
            $permissions[] = [
                "id" => $permission->getIdValue(),
                "name" => $permission->getNameValue(),
                "isActive" => $permission->getIsActive(),
            ];
        }
        $sectors = [];
        foreach ($this->sectorCollection->fetchAll() as $sector) {
            $sectors[] = [
                "id" => $sector->getIdValue(),
                "name" => $sector->getNameValue(),
                "isActive" => $sector->getIsActive(),
            ];
        }
        $sectorPermissions = [];
        foreach ($this->sectorPermissionCollection->fetchAll() as $sectorPermission) {
            $sectorPermissions[] = [
                "id" => $sectorPermission->getIdValue(),
                "sectorId" => $sectorPermission->getSectorIdValue(),
                "permissionId" => $sectorPermission->getPermissionIdValue(),
            ];
        }
        return [
            "userId" => $this->userId->getValue(),
            "username" => $this->username->getValue(),
            "permissions" => $permissions,
            "sectors" => $sectors,
            "sectorPermissions" => $sectorPermissions,
        ];
    }

    public function toSnakeCaseArray(): array
    {
        $permissions = [];
        foreach ($this->permissionCollection->fetchAll() as $permission) {
            $permissions[] = [
                "id" => $permission->getIdValue(),
                "name" => $permission->getNameValue(),
                "is_active" => $permission->getIsActive(),
            ];
        }
        $sectors = [];
        foreach ($this->sectorCollection->fetchAll() as $sector) {
            $sectors[] = [
                "id" => $sector->getIdValue(),
                "name" => $sector->getNameValue(),
                "is_active" => $sector->getIsActive(),
            ];
        }
        $sectorPermissions = [];
        foreach ($this->sectorPermissionCollection->fetchAll() as $sectorPermission) {
            $sectorPermissions[] = [
                "id" => $sectorPermission->getIdValue(),
                "sector_id" => $sectorPermission->getSectorIdValue(),
                "permission_id" => $sectorPermission->getPermissionIdValue(),
            ];
        }
        return [
            "user_id" => $this->userId->getValue(),
            "username" => $this->username->getValue(),
            "permissions" => $permissions,
            "sectors" => $sectors,
            "sector_permissions" => $sectorPermissions,
        ];
    }
}
