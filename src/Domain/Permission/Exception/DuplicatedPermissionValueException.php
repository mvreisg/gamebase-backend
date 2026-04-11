<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\Exception;

use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;

class DuplicatedPermissionValueException extends \Exception
{
    public function __construct(
        PermissionValue $value
    ) {
        parent::__construct(
            "The permission value '{$value->getValue()->value}' is duplicated."
        );
    }
}
