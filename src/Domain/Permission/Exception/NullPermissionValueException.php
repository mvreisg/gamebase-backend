<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Exception;

class NullPermissionValueException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            "The permission value of the permission is null."
        );
    }
}
