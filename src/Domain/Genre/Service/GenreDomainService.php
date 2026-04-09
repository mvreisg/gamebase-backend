<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Genre\Service;

use Mvreisg\GamebaseBackend\Domain\Genre\Exception\GenreNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Genre\Repository\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\DuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class GenreDomainService
{
    private GenreRepositoryInterface $repository;

    public function __construct(
        GenreRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function ensureNameIsUnique(?Id $id = null, Name $name): void
    {
        $hasDuplicatedNames = $this->repository->checkDuplicatedNames(
            $id,
            $name
        );

        if ($hasDuplicatedNames) {
            throw new DuplicatedNameException(
                $name
            );
        }
    }

    public function ensureGenreExists(Id $id): void
    {
        $doesExist = $this->repository->checkIfExists($id);

        if ($doesExist === false) {
            throw new GenreNotFoundException(
                $id
            );
        }
    }
}
