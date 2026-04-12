<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Repository;

use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Collection\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

interface SectorRepositoryInterface
{
    public function insert(Sector $sector): Sector;

    public function update(Sector $sector): bool;

    public function findById(Id $id): ?Sector;

    public function findAll(): ?SectorCollection;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function checkIfExists(Id $id): bool;

    public function checkDuplicatedNames(?Id $id = null, Name $name): bool;

    public function checkDuplicatedValues(?Id $id = null, SectorValue $value): bool;
}
