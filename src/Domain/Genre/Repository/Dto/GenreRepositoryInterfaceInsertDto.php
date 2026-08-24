<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Genre\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

final readonly class GenreRepositoryInterfaceInsertDto
{
    public function __construct(
        public Name $name,
        public bool $isActive,
    ) {
    }
}
