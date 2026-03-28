<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Entities\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;

interface GenreRepositoryInterface
{
    public function insert(Genre $genre): Genre;

    public function update(Genre $genre): bool;

    public function setIsActive(Id $id, bool $isActive): bool;

    public function findById(Id $id): Genre;

    public function findAll(): GenreCollection;

    public function checkIfExists(Id $id): void;

    public function checkDuplicatedNames(Name $name): void;
}
