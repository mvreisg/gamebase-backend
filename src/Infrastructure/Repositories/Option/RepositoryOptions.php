<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Option;

class RepositoryOptions
{
    private string $database;

    public function __construct(string $database)
    {
        $this->database = $database;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }
}
