<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions;

class RepositoryStatementFetchFailureException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            "The statement fetch has failed."
        );
    }
}
