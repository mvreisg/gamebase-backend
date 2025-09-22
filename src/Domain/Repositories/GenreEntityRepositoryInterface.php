<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\GenreEntity;

interface GenreEntityRepositoryInterface
{
    public function insert(GenreEntity $genreEntity): GenreEntity;

    public function update(GenreEntity $genreEntity): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): GenreEntity|null;

    public function findAll(): array;

    public function checkDuplicatedNames(string $name): void;
}
