<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Game\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

final readonly class GameServiceInsertDto
{
    public function __construct(
        public Name $name,
        public bool $isActive,
    ) {
    }
}
