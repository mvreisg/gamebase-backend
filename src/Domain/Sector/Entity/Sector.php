<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Entity;

use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class Sector
{
    private Id $id;
    private Name $name;
    private SectorValue $value;
    private bool $isActive;

    public function __construct(
        Id $id,
        Name $name,
        SectorValue $value,
        bool $isActive
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
        $this->isActive = $isActive;
    }

    public static function create(
        Id $id,
        Name $name,
        SectorValue $value,
        bool $isActive
    ): self {
        return new self(
            $id,
            $name,
            $value,
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

    public function getSectorValue(): SectorValue
    {
        return $this->value;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function equals(SectorType $type): bool
    {
        return $type->value === $this->getSectorValue()->getValue()->value;
    }
}
