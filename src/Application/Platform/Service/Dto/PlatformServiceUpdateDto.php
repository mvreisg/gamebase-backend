<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Platform\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

final readonly class PlatformServiceUpdateDto
{
    public function __construct(
        public Id $id,
        public Name $name,
        public bool $isActive,
    ) {
    }
}
