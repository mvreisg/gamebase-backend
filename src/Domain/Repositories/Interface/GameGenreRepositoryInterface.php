<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Interface;

use Mvreisg\GamebaseBackend\Domain\Data\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Data\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Id;

interface GameGenreRepositoryInterface
{
    public function insert(GameGenre $gameGenre): GameGenre;

    public function update(GameGenre $gameGenre): bool;

    public function delete(Id $id): bool;

    public function findById(Id $id): GameGenre;

    public function findAll(): GameGenreCollection;

    public function checkIfExists(Id $id): void;
}
