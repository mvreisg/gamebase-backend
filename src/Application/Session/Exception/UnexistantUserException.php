<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Session\Exception;

use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class UnexistantUserException extends \Exception
{
    public function __construct(
        Username $username
    ) {
        parent::__construct(
            "Unexistant username: {$username->getValue()}"
        );
    }
}
