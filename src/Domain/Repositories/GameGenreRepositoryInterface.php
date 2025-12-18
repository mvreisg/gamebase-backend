<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre\GameGenre;

interface GameGenreRepositoryInterface
{
    public function insert(GameGenre $gameGenre): GameGenre;

    public function update(GameGenre $gameGenre): bool;

    public function delete(GameGenre $gameGenre): bool;

    public function findById(int $id): GameGenre;

    public function findAll(): array;

    public function checkIfExists(int $id): void;
}
