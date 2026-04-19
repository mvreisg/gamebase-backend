<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\Interface;

interface DatabaseRepositoryInterface
{
    public function exists(string $databaseName): bool;

    public function create(string $databaseName): void;

    public function drop(string $databaseName): void;
}
