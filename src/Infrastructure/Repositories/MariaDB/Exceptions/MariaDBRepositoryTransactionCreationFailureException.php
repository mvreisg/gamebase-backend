<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions;

use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryTransactionCreationFailureException;

class MariaDBRepositoryTransactionCreationFailureException extends RepositoryTransactionCreationFailureException
{
    public function __construct()
    {
        parent::__construct();
    }
}
