<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Platform\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

final readonly class PlatformRepositoryInterfaceUpdateDto
{
    public function __construct(
        public Id $id,
        public Name $name,
        public bool $isActive,
    ) {
    }
}
