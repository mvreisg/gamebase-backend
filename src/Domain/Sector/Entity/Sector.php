<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Entity;

use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Sector\Exception\NullSectorValueException;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIsActiveException;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class Sector
{
    private ?Id $id;
    private Name $name;
    private SectorValue $value;
    private bool $isActive;

    public static function create(
        ?Id $id = null,
        ?Name $name = null,
        ?SectorValue $value = null,
        ?bool $isActive = null
    ): self {
        return new self(
            $id,
            $name,
            $value,
            $isActive
        );
    }

    public function __construct(
        ?Id $id = null,
        ?Name $name = null,
        ?SectorValue $value = null,
        ?bool $isActive = null
    ) {
        $this->id = $id;
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
        if ($this->name === null) {
            throw new NullNameException(
                Sector::class
            );
        }
        return $this->name;
    }

    public function getSectorValue(): SectorValue
    {
        if ($this->value === null) {
            throw new NullSectorValueException();
        }
        return $this->value;
    }

    public function getIsActive(): bool
    {
        if ($this->isActive === null) {
            throw new NullIsActiveException(
                Sector::class
            );
        }
        return $this->isActive;
    }

    public function equals(SectorType $type): bool
    {
        return $type === $this->getSectorValue()->getValue();
    }
}
