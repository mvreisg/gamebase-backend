<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Genre\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

final readonly class GenreServiceInsertDto
{
    public function __construct(
        public Name $name,
        public bool $isActive,
    ) {
    }
}
