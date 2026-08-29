<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class GameGenreRepositoryInterfaceUpdateDto
{
    public function __construct(
        public Id $id,
        public Id $gameId,
        public Id $genreId,
    ) {
    }
}
