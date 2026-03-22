<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;

class Game
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

    public function getIdValue(): int
    {
        if ($this->id === null) {
            throw new EntityException(
                "The id is null."
            );
        }
        return $this->id->getValue();
    }

    public function getNameValue(): string
    {
        if ($this->name === null) {
            throw new EntityException(
                "The name is null"
            );
        }
        return $this->name->getValue();
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
