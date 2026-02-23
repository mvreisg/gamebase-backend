<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

use Mvreisg\GamebaseBackend\Domain\Data\Exceptions\DataException;

class Permission
{
    private ?Id $id;
    private Name $name;
    private PermissionValue $value;
    private bool $isActive;

    public function __construct(Name $name, PermissionValue $value, bool $isActive)
    {
        $this->id = null;
        $this->name = $name;
        $this->value = $value;
        $this->isActive = $isActive;
    }

    public function setId(Id $id): void
    {
        $this->id = $id;
    }

    public function getIdValue(): int
    {
        if ($this->id === null) {
            throw new DataException(
                "The id is null."
            );
        }
        return $this->id->getValue();
    }

    public function getNameValue(): string
    {
        if ($this->name === null) {
            throw new DataException(
                "The name is null"
            );
        }
        return $this->name->getValue();
    }

    public function getPermissionValue(): string
    {
        if ($this->value === null) {
            throw new DataException(
                "The permission value is null"
            );
        }
        return $this->value->getValue();
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
