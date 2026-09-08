<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Entity;

use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class Sector
{
    private Id $id;
    private Name $name;
    private SectorValue $value;
    private bool $isActive;

    public function __construct(
        Id $id,
        Name $name,
        SectorValue $value,
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
        SectorValue $value,
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

    public function getSectorValue(): SectorValue
    {
        return $this->value;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function equals(SectorValue $other): bool
    {
        return $this->getSectorValue()->getValue() === $other->getValue();
    }

    public function allow(Permission $permission): bool
    {
        switch ($this) {
            case SectorType::GameGenre:
            case SectorType::GamePlatform:
            case SectorType::UserSectorPermission:
                return $permission->getPermissionValue()->getValue() === PermissionType::Delete->value;
            case SectorType::Game:
            case SectorType::Genre:
            case SectorType::Platform:
            case SectorType::User:
            case SectorType::Sector:
            case SectorType::Permission:
                return $permission->getPermissionValue()->getValue() === PermissionType::Activate->value;
            default:
                return true;
        }
    }
}
