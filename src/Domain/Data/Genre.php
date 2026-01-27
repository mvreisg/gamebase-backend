<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class Genre
{
    private ?Id $id;
    private ?Name $name;
    private bool $isActive;

    public function __construct(?Id $id = null, ?Name $name = "", bool $isActive = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
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

    public function getNameValue(): string
    {
        if ($this->name === null) {
            throw new \InvalidArgumentException(
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
