<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class SectorPermission
{
    private ?Id $id;
    private Id $sectorId;
    private Id $permissionId;

    public function __construct(Id $sectorId, Id $permissionId)
    {
        $this->id = null;
        $this->sectorId = $sectorId;
        $this->permissionId = $permissionId;
    }

    public function setId(Id $id): void
    {
        $this->id = $id;
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

    public function getSectorIdValue(): int
    {
        if ($this->sectorId === null) {
            throw new \InvalidArgumentException(
                "The sectorId is null."
            );
        }
        return $this->sectorId->getValue();
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
