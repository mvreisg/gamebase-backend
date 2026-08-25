<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

final readonly class SectorRepositoryInterfaceUpdateDto
{
    public function __construct(
        public Id $id,
        public Name $name,
        public SectorValue $value,
        public bool $isActive
    ) {
    }
}
