<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Sector\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

final readonly class SectorServiceInsertDto
{
    public function __construct(
        public Name $name,
        public SectorValue $value,
        public bool $isActive
    ) {
    }
}
