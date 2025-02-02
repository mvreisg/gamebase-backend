<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform;

interface PlatformRepositoryInterface
{
    public function insert(Platform $platform): Platform;

    public function edit(Platform $platform): bool;

    public function delete(int $id): bool;

    public function findById(int $id): Platform|null;

    public function findAll(): array;

    public function hasDuplicatedNames(string $name): bool;
}
