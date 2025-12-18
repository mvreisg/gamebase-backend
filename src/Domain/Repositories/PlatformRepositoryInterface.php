<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform\Platform;

interface PlatformRepositoryInterface
{
    public function insert(Platform $platform): Platform;

    public function update(Platform $platform): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): Platform;

    public function findAll(): array;

    public function checkIfExists(int $id): void;

    public function checkDuplicatedNames(string $name): void;
}
