<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\Entity;

use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
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

    public function equals(PermissionType $type): bool
    {
        return $type->value === $this->getPermissionValue()->getValue()->value;
    }
}
