<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\Interface;

interface DatabaseRepositoryInterface
{
    public function exists(string $database): bool;

    public function create(string $database): bool;

    public function drop(string $database): bool;
}
