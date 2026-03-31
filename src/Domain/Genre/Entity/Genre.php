<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Genre\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class Genre
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
            throw new NullIdException(
                Genre::class
            );
        }
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
