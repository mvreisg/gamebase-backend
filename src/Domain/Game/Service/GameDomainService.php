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

    public function ensureNameIsUniqueOnInsert(Name $name): void
    {
        $id = $this->repository->checkIfNameExists(
            $name
        );

        if ($id) {
            throw new DuplicatedNameException(
                $name
            );
        }
    }

    public function ensureNameIsUniqueOnUpdate(Name $name, Id $id): void
    {
        $existantId = $this->repository->checkIfNameExists(
            $name
        );

        if ($existantId === null) {
            return;
        }

        if ($existantId->getValue() !== $id->getValue()) {
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
