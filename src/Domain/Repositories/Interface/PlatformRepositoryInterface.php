<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Platform;
use Mvreisg\GamebaseBackend\Domain\Data\PlatformCollection;

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
