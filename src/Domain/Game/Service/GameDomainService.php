<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Game\Service;

use Mvreisg\GamebaseBackend\Domain\Game\Exception\GameNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Game\Repository\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\DuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class GameDomainService
{
    private GameRepositoryInterface $repository;

    public function __construct(
        GameRepositoryInterface $repository
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

    public function ensureGameExists(Id $id): void
    {
        $doesExist = $this->repository->checkIfExists($id);

        if ($doesExist === false) {
            throw new GameNotFoundException(
                $id
            );
        }
    }
}
