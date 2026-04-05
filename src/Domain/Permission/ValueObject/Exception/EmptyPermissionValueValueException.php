<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\Exception;

class EmptyPermissionValueValueException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Empty PermissionValue value.");
    }
}
