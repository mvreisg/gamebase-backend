<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Platform\Service;

use Mvreisg\GamebaseBackend\Domain\Platform\Exception\PlatformNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\DuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class PlatformDomainService
{
    private PlatformRepositoryInterface $repository;

    public function __construct(
        PlatformRepositoryInterface $repository
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

    public function ensurePlatformExists(Id $id): void
    {
        $doesExist = $this->repository->checkIfExists($id);

        if ($doesExist === false) {
            throw new PlatformNotFoundException(
                $id
            );
        }
    }
}
