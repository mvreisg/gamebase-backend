<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;

class Platform
{
    private ?Id $id;
    private Name $name;
    private bool $isActive;

    public function __construct(Name $name, bool $isActive)
    {
        $this->id = null;
        $this->name = $name;
        $this->isActive = $isActive;
    }

    public function setId(Id $id): void
    {
        $this->id = $id;
    }

    public function getId(): Id
    {
        if ($this->id === null) {
            throw new EntityException(
                "The id is null."
            );
        }
        return $this->id;
    }

    public function getName(): Name
    {
        if ($this->name === null) {
            throw new EntityException(
                "The name is null"
            );
        }
        return $this->name;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
