<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class UserPermission
{
    private ?Id $id;
    private Id $userId;
    private Id $permissionId;

    public function __construct(?Id $id = null, Id $userId, Id $permissionId)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->permissionId = $permissionId;
    }

    public function getIdValue(): int
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(
                "The id is null."
            );
        }
        return $this->id->getValue();
    }

    public function getUserIdValue(): int
    {
        if ($this->userId === null) {
            throw new \InvalidArgumentException(
                "The userId is null."
            );
        }
        return $this->userId->getValue();
    }

    public function getPermissionIdValue(): int
    {
        if ($this->permissionId === null) {
            throw new \InvalidArgumentException(
                "The permissionId is null."
            );
        }
        return $this->permissionId->getValue();
    }
}
