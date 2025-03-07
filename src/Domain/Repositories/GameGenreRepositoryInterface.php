<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;

interface GameGenreRepositoryInterface
{
    public function insert(GameGenre $gameGenre): GameGenre;

    public function update(GameGenre $gameGenre): bool;

    public function delete(GameGenre $gameGenre): bool;

    public function findById(int $id): GameGenre|null;

    public function findAll(): array;
}
