<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class GamePlatformRepositoryInterfaceUpdateDto
{
    public function __construct(
        public Id $id,
        public Id $gameId,
        public Id $platformId,
    ) {
    }
}
