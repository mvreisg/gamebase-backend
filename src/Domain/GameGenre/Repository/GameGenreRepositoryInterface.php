<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GameGenre\Repository;

use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\Collection\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\GameGenre;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\Dto\GameGenreRepositoryInterfaceInsertDto;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\Dto\GameGenreRepositoryInterfaceUpdateDto;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

interface GameGenreRepositoryInterface
{
    public function insert(GameGenreRepositoryInterfaceInsertDto $dto): GameGenre;

    public function update(GameGenreRepositoryInterfaceUpdateDto $dto): bool;

    public function delete(Id $id): bool;

    public function findById(Id $id): ?GameGenre;

    public function findAll(): ?GameGenreCollection;

    public function checkIfExists(Id $id): bool;
}
