<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Platform\Repository;

use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Collection\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

interface PlatformRepositoryInterface
{
    public function insert(Platform $platform): Platform;

    public function update(Platform $platform): bool;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function findById(Id $id): ?Platform;

    public function findAll(): ?PlatformCollection;

    public function checkIfExists(Id $id): bool;

    public function checkDuplicatedNames(?Id $id = null, Name $name): bool;
}
