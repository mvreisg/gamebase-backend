<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\Exception;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class UserNotFoundException extends \Exception
{
    public function __construct(Id $id)
    {
        parent::__construct(
            "The user with id '{$id->getValue()}' was not found."
        );
    }
}
