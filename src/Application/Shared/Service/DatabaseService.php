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

    public function exists(string $database): bool
    {
        return $this->repository->exists($database);
    }

    public function create(string $database): bool
    {
        return $this->repository->create($database);
    }

    public function drop(string $database): bool
    {
        return $this->repository->drop($database);
    }
}
