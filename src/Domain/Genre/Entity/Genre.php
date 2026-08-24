<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Genre\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class Genre
{
    private Id $id;
    private Name $name;
    private bool $isActive;

    public function __construct(
        Id $id,
        Name $name,
        bool $isActive
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
    }

    public static function create(
        Id $id,
        Name $name,
        bool $isActive
    ): self {
        return new self(
            $id,
            $name,
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

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
