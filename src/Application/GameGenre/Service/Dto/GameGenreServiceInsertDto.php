<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\GameGenre\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class GameGenreServiceInsertDto
{
    public function __construct(
        public Id $gameId,
        public Id $genreId,
    ) {
    }
}
