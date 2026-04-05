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

    public function ensureNameIsUnique(Name $name): void
    {
        $hasDuplicatedNames = $this->repository->checkDuplicatedNames(
            $name
        );

        if ($hasDuplicatedNames) {
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
