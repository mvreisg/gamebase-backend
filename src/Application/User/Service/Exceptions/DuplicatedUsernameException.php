<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\User\Service\Exceptions;

use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class DuplicatedUsernameException extends \Exception
{
    public function __construct(Username $username)
    {
        parent::__construct(
            "The username '{$username->getValue()}' is already in use."
        );
    }
}
