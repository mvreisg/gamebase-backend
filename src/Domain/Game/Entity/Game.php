<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Game\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIsActiveException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class Game
{
    private ?Id $id;
    private ?Name $name;
    private ?bool $isActive;

    public function __construct(
        ?Id $id,
        ?Name $name,
        ?bool $isActive
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
    }

    public static function create(
        ?Id $id,
        ?Name $name,
        ?bool $isActive
    ): Game {
        return new self(
            $id,
            $name,
            $isActive
        );
    }

    public static function createFromIdOnly(
        Id $id
    ): self {
        return self::create(
            $id,
            null,
            null
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
                Game::class
            );
        }
        return $this->id;
    }

    public function getName(): Name
    {
        if ($this->name === null) {
            throw new NullNameException(
                Game::class
            );
        }
        return $this->name;
    }

    public function getIsActive(): bool
    {
        if ($this->isActive === null) {
            throw new NullIsActiveException(
                Game::class
            );
        }
        return $this->isActive;
    }
}
