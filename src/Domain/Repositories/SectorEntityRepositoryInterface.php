<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\SectorEntity;

interface SectorEntityRepositoryInterface
{
    public function insert(SectorEntity $sectorEntity): SectorEntity;

    public function update(SectorEntity $sectorEntity): bool;

    public function findById(int $id): SectorEntity|null;

    public function findAll(): array;

    public function setIsActive(int $id, bool $isActive): bool;

    public function checkDuplicatedNames(string $name): void;
}
