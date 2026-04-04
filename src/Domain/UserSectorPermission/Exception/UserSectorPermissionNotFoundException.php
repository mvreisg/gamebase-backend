<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Exception;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class UserSectorPermissionNotFoundException extends \Exception
{
    public function __construct(Id $id)
    {
        parent::__construct(
            "The user sector permission with id '{$id->getValue()}' was not found."
        );
    }
}
