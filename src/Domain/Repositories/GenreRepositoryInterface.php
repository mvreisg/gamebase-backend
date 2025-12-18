<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre\Genre;

interface GenreRepositoryInterface
{
    public function insert(Genre $genre): Genre;

    public function update(Genre $genre): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    public function findById(int $id): Genre;

    public function findAll(): array;

    public function checkIfExists(int $id): void;

    public function checkDuplicatedNames(string $name): void;
}
