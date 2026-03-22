<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Entities\PlatformCollection;

interface PlatformRepositoryInterface
{
    public function insert(Platform $platform): Platform;

    public function update(Platform $platform): bool;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function findById(Id $id): Platform;

    public function findAll(): PlatformCollection;

    public function checkIfExists(Id $id): void;

    public function checkDuplicatedNames(Name $name): void;
}
