<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorCollection;

interface SectorRepositoryInterface
{
    public function insert(Sector $sector): Sector;

    public function update(Sector $sector): bool;

    public function findById(Id $id): Sector;

    public function findAll(): SectorCollection;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function checkIfExists(Id $id): void;

    public function checkDuplicatedNames(Name $name): void;
}
