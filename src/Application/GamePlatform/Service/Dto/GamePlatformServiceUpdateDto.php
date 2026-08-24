<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\GamePlatform\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class GamePlatformServiceUpdateDto
{
    public function __construct(
        public Id $id,
        public Id $gameId,
        public Id $platformId,
    ) {
    }
}
