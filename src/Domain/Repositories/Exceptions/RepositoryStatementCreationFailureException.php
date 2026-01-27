<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions;

class RepositoryStatementCreationFailureException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            "The statement could not be created."
        );
    }
}
