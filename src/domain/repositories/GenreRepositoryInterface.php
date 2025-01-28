<?php
namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre;

interface GenreRepositoryInterface
{
    public function insert(Genre $genre): Genre;
    public function edit(Genre $genre): bool;
    public function delete(int $id): bool;
    public function findById(int $id): Genre|null;
    public function findAll(): array;
    public function hasDuplicatedNames(string $name): bool;
}
