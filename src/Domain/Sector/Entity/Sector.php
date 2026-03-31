<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Entity;

use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class Sector
{
    private ?Id $id;
    private Name $name;
    private SectorValue $value;
    private bool $isActive;

    public function __construct(Name $name, SectorValue $value, bool $isActive)
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

    public function getId(): Id
    {
        if ($this->id === null) {
            throw new NullIdException(
                Sector::class
            );
        }
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getSectorValue(): SectorValue
    {
        return $this->value;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
