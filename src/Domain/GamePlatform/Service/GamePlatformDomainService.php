<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GamePlatform\Service;

use Mvreisg\GamebaseBackend\Domain\GamePlatform\Exception\GamePlatformNotFoundException;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GamePlatformDomainService
{
    private GamePlatformRepositoryInterface $repository;

    public function __construct(
        GamePlatformRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function ensureGamePlatformExists(Id $id): void
    {
        $doesExist = $this->repository->checkIfExists($id);

        if ($doesExist === false) {
            throw new GamePlatformNotFoundException(
                $id
            );
        }
    }
}
