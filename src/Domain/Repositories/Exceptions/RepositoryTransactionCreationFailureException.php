<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions;

class RepositoryTransactionCreationFailureException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            "The transaction could not be created."
        );
    }
}
