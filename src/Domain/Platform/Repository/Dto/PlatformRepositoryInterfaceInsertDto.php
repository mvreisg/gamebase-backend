<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Platform\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

final readonly class PlatformRepositoryInterfaceInsertDto
{
    public function __construct(
        public Name $name,
        public bool $isActive,
    ) {
    }
}
