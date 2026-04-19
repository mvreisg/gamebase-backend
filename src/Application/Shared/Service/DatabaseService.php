<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Shared\Service;

use Mvreisg\GamebaseBackend\Domain\Shared\Interface\DatabaseRepositoryInterface;

class DatabaseService
{
    private DatabaseRepositoryInterface $repository;

    public function __construct(DatabaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function exists(string $databaseName): bool
    {
        return $this->repository->exists($databaseName);
    }

    public function create(string $databaseName): void
    {
        $this->repository->create($databaseName);
    }

    public function drop(string $databaseName): void
    {
        $this->repository->drop($databaseName);
    }
}
