<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\User\Service\Exceptions;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class DuplicatedUserException extends \Exception
{
    public function __construct(Id $id)
    {
        parent::__construct(
            "The user with id '{$id->getValue()}' already exists."
        );
    }
}
