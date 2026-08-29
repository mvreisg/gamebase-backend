<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Repository;

use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Collection\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\Dto\SectorRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\Dto\SectorRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

interface SectorRepositoryInterface
{
    public function insert(SectorRepositoryInterfaceInsertDto $dto): Sector;

    public function update(SectorRepositoryInterfaceUpdateDto $dto): bool;

    public function findById(Id $id): ?Sector;

    public function findAll(): ?SectorCollection;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function checkIfExists(Id $id): bool;

    public function checkIfNameExists(Name $name): ?Id;

    public function checkIfValueExists(SectorValue $value): ?Id;
}
