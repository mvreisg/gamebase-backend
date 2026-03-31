<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Genre\Repository;

use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Collection\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

interface GenreRepositoryInterface
{
    public function insert(Genre $genre): Genre;

    public function update(Genre $genre): bool;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function findById(Id $id): ?Genre;

    public function findAll(): ?GenreCollection;

    public function checkIfExists(Id $id): bool;

    public function checkDuplicatedNames(Name $name): bool;
}
