<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GameGenre\Service;

use Mvreisg\GamebaseBackend\Domain\GameGenre\Exception\GameGenreNotFoundException;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GameGenreDomainService
{
    private GameGenreRepositoryInterface $repository;

    public function __construct(
        GameGenreRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function ensureGameGenreExists(Id $id): void
    {
        $doesExist = $this->repository->checkIfExists($id);

        if ($doesExist === false) {
            throw new GameGenreNotFoundException(
                $id
            );
        }
    }
}
