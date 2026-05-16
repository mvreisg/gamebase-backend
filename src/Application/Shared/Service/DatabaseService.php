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
            return $this->repository->exists($database);
        } catch (\Exception $e) {
            $this->logger->error(
                "An error occurred while checking if database exists",
                [
                    "exception" => $e,
                    "database" => $database
                ]
            );
            throw $e;
        }
    }

    public function create(string $database): bool
    {
        try {
            return $this->repository->create($database);
        } catch (\Exception $e) {
            $this->logger->error(
                "An error occurred while creating database",
                [
                    "exception" => $e,
                    "database" => $database
                ]
            );
            throw $e;
        }
    }

    public function drop(string $database): bool
    {
        try {
            return $this->repository->drop($database);
        } catch (\Exception $e) {
            $this->logger->error(
                "An error occurred while dropping database",
                [
                    "exception" => $e,
                    "database" => $database
                ]
            );
            throw $e;
        }
    }
}
