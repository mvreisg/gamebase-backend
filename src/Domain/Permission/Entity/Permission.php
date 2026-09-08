<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\Entity;

use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class Permission
{
    private Id $id;
    private Name $name;
    private PermissionValue $value;
    private bool $isActive;

    public function __construct(
        Id $id,
        Name $name,
        PermissionValue $value,
        bool $isActive
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
        $this->isActive = $isActive;
    }

    public static function create(
        Id $id,
        Name $name,
        PermissionValue $value,
        bool $isActive
    ): self {
        return new self(
            $id,
            $name,
            $value,
            $isActive
        );
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getPermissionValue(): PermissionValue
    {
        return $this->value;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function equals(PermissionValue $other): bool
    {
        return $this->getPermissionValue()->getValue() === $other->getValue();
    }

    public function allow(Sector $sector): bool
    {
        switch ($this) {
            case PermissionType::Delete:
                return
                    $sector->getSectorValue()->getValue() === SectorType::GameGenre ||
                    $sector->getSectorValue()->getValue() === SectorType::GamePlatform ||
                    $sector->getSectorValue()->getValue() === SectorType::UserSectorPermission;
            case PermissionType::Activate:
                return
                    $sector->getSectorValue()->getValue() === SectorType::Game ||
                    $sector->getSectorValue()->getValue() === SectorType::Genre ||
                    $sector->getSectorValue()->getValue() === SectorType::Platform ||
                    $sector->getSectorValue()->getValue() === SectorType::User ||
                    $sector->getSectorValue()->getValue() === SectorType::Sector ||
                    $sector->getSectorValue()->getValue() === SectorType::Permission;
            default:
                return true;
        }
    }
}
