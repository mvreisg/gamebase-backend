<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Data;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Domain\Data\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\UserSectorPermissionCollection;

class AuthenticationData
{
    private Id $userId;
    private Username $username;
    private UserSectorPermissionCollection $userSectorPermissionCollection;

    public function __construct(
        Id $userId,
        Username $username,
        UserSectorPermissionCollection $userSectorPermissionCollection
    ) {
        $this->userId = $userId;
        $this->username = $username;
        $this->userSectorPermissionCollection = $userSectorPermissionCollection;
    }

    public static function toObject(\stdClass $data): self
    {
        $userSectorPermissions = [];
        foreach ($data->userSectorPermissions as $userSectorPermission) {
            $value = new UserSectorPermission(
                Id::make($userSectorPermission->userId),
                Id::make($userSectorPermission->sectorId),
                Id::make($userSectorPermission->permissionId)
            );
            $value->setId(Id::make($userSectorPermission->id));
            $userSectorPermissions[] = $value;
        }
        return new self(
            Id::make($data->userId),
            Username::make($data->username),
            new UserSectorPermissionCollection($userSectorPermissions)
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

    public function getUserSectorPermissionCollection(): UserSectorPermissionCollection
    {
        return $this->userSectorPermissionCollection;
    }

    public function toArray(): array
    {
        $userSectorPermissions = [];
        foreach ($this->userSectorPermissionCollection->fetchAll() as $userSectorPermission) {
            $userSectorPermissions[] = [
                "id" => $userSectorPermission->getIdValue(),
                "userId" => $userSectorPermission->getUserIdValue(),
                "sectorId" => $userSectorPermission->getSectorIdValue(),
                "permissionId" => $userSectorPermission->getPermissionIdValue(),
            ];
        }
        return [
            "userId" => $this->userId->getValue(),
            "username" => $this->username->getValue(),
            "userSectorPermissions" => $userSectorPermissions,
        ];
    }

    public function toSnakeCaseArray(): array
    {
        $userSectorPermissions = [];
        foreach ($this->userSectorPermissionCollection->fetchAll() as $userSectorPermission) {
            $userSectorPermissions[] = [
                "id" => $userSectorPermission->getIdValue(),
                "user_id" => $userSectorPermission->getUserIdValue(),
                "sector_id" => $userSectorPermission->getSectorIdValue(),
                "permission_id" => $userSectorPermission->getPermissionIdValue(),
            ];
        }
        return [
            "user_id" => $this->userId->getValue(),
            "username" => $this->username->getValue(),
            "user_sector_permissions" => $userSectorPermissions,
        ];
    }
}
