<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Shared\Service;

use Mvreisg\GamebaseBackend\Domain\Shared\Interface\DatabaseRepositoryInterface;
use Psr\Log\LoggerInterface;

class DatabaseService
{
    private DatabaseRepositoryInterface $repository;
    private LoggerInterface $logger;

    public function __construct(
        DatabaseRepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function exists(string $database): bool
    {
        try {
            $doesTheDatabaseExists = $this->repository->exists($database);
            $this->logger->info("Database exists?", [
                "exists" => $doesTheDatabaseExists
            ]);
            return $doesTheDatabaseExists;
        } catch (\Exception $e) {
            $this->logger->error(
                "An error occurred while checking if database exists",
                [
                    "exception" => $e->getMessage(),
                    "database" => $database
                ]
            );
            throw $e;
        }
    }

    public function create(string $database): bool
    {
        try {
            $wasCreated = $this->repository->create($database);
            $this->logger->info("Database creation attempt!", [
                "wasCreated" => $wasCreated
            ]);
            return $wasCreated;
        } catch (\Exception $e) {
            $this->logger->error(
                "An error occurred while creating database",
                [
                    "exception" => $e->getMessage(),
                    "database" => $database
                ]
            );
            throw $e;
        }
    }

    public function drop(string $database): bool
    {
        try {
            $wasDropped = $this->repository->drop($database);
            $this->logger->info("Database drop attempt!", [
                "wasDropped" => $wasDropped
            ]);
            return $wasDropped;
        } catch (\Exception $e) {
            $this->logger->error(
                "An error occurred while dropping database",
                [
                    "exception" => $e->getMessage(),
                    "database" => $database
                ]
            );
            throw $e;
        }
    }
}
