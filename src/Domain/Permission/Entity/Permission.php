<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\Entity;

use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\Exception\NullPermissionValueException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIsActiveException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class Permission
{
    private ?Id $id;
    private Name $name;
    private PermissionValue $value;
    private bool $isActive;

    public function __construct(
        ?Id $id = null,
        ?Name $name = null,
        ?PermissionValue $value = null,
        ?bool $isActive = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
        $this->isActive = $isActive;
    }

    public static function create(
        ?Id $id = null,
        ?Name $name = null,
        ?PermissionValue $value = null,
        ?bool $isActive = null
    ): self {
        return new self(
            $id,
            $name,
            $value,
            $isActive
        );
    }

    public function setId(Id $id): void
    {
        $this->id = $id;
    }

    public function getId(): Id
    {
        if ($this->id === null) {
            throw new NullIdException(
                Permission::class
            );
        }
        return $this->id;
    }

    public function getName(): Name
    {
        if ($this->name === null) {
            throw new NullNameException(
                Permission::class
            );
        }
        return $this->name;
    }

    public function getPermissionValue(): PermissionValue
    {
        if ($this->value === null) {
            throw new NullPermissionValueException();
        }
        return $this->value;
    }

    public function getIsActive(): bool
    {
        if ($this->isActive === null) {
            throw new NullIsActiveException(
                Permission::class
            );
        }
        return $this->isActive;
    }

    public function equals(PermissionType $type): bool
    {
        return $type === $this->getPermissionValue()->getValue();
    }
}
