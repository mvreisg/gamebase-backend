<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities\Sector;

use Mvreisg\GamebaseBackend\Domain\Entities\Sector\Exceptions\SectorInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector\Exceptions\SectorInvalidNameException;

class Sector
{
    private ?int $id;
    private ?string $name;
    private bool $isActive;

    public function __construct(?int $id = null, ?string $name = null, bool $isActive = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function validateId(): void
    {
        if ($this->getId() <= 0) {
            throw new SectorInvalidIdException(
                'The id must be greater than zero!'
            );
        }
    }

    public function validateName(): void
    {
        $originalName = trim($this->getName());

        if ($originalName === '') {
            throw new SectorInvalidNameException(
                'The name is empty!'
            );
        }

        $isInvalid = preg_match('/[^a-zA-Z0-9]/', $originalName);
        if ($isInvalid) {
            throw new SectorInvalidNameException(
                'The name is invalid!'
            );
        }

        $this->setName($originalName);
    }
}
