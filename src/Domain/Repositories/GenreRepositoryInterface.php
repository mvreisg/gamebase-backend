<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre;

interface GenreRepositoryInterface
{
    public function insert(Genre $genre): Genre;

    public function update(Genre $genre): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): Genre|null;

    public function findAll(): array;

    public function hasDuplicatedNames(string $name): bool;
}
