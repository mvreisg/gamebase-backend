<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\GameGenre\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class GameGenreServiceUpdateDto
{
    public function __construct(
        public Id $id,
        public Id $gameId,
        public Id $genreId,
    ) {
    }
}
