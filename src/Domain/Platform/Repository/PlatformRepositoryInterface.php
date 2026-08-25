<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Platform\Repository;

use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Collection\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\Dto\PlatformRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\Dto\PlatformRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

interface PlatformRepositoryInterface
{
    public function insert(PlatformRepositoryInterfaceInsertDto $dto): Platform;

    public function update(PlatformRepositoryInterfaceUpdateDto $dto): bool;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function findById(Id $id): ?Platform;

    public function findAll(): ?PlatformCollection;

    public function checkIfExists(Id $id): bool;

    public function checkIfNameExists(Name $name): ?Id;
}
