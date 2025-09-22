<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenreEntity;

interface GameGenreEntityRepositoryInterface
{
    public function insert(GameGenreEntity $gameGenreEntity): GameGenreEntity;

    public function update(GameGenreEntity $gameGenreEntity): bool;

    public function delete(GameGenreEntity $gameGenreEntity): bool;

    public function findById(int $id): GameGenreEntity|null;

    public function findAll(): array;
}
