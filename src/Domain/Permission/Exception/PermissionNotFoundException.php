<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\Exception;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class PermissionNotFoundException extends \Exception
{
    public function __construct(Id $id)
    {
        parent::__construct(
            "The permission with id '{$id->getValue()}' was not found."
        );
    }
}
