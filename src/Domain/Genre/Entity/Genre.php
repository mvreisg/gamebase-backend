<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Genre\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIsActiveException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class Genre
{
    private ?Id $id;
    private ?Name $name;
    private ?bool $isActive;

    public function __construct(
        ?Id $id = null,
        ?Name $name = null,
        ?bool $isActive = null
    ) {
        $this->id = $id;
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
        if ($this->name === null) {
            throw new NullNameException(
                Genre::class
            );
        }
        return $this->name;
    }

    public function getIsActive(): bool
    {
        if ($this->isActive === null) {
            throw new NullIsActiveException(
                Genre::class
            );
        }
        return $this->isActive;
    }
}
