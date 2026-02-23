<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\Options;

class PdoRepositoryConnectionOptions
{
    private bool $useDatabase;

    public function __construct(bool $useDatabase)
    {
        $this->useDatabase = $useDatabase;
    }

    public function getUseDatabase(): bool
    {
        return $this->useDatabase;
    }
}
