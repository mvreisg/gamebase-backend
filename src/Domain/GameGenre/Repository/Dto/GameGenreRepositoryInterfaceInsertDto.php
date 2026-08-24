<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class GameGenreRepositoryInterfaceInsertDto
{
    public function __construct(
        public Id $gameId,
        public Id $genreId,
    ) {
    }
}
