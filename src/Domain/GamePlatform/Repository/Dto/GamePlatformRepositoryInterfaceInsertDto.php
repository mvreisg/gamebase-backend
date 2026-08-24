<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class GamePlatformRepositoryInterfaceInsertDto
{
    public function __construct(
        public Id $gameId,
        public Id $platformId,
    ) {
    }
}
