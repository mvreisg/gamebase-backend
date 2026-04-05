<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Platform\Exception;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class PlatformNotFoundException extends \Exception
{
    public function __construct(Id $id)
    {
        parent::__construct(
            "The platform with id '{$id->getValue()}' was not found."
        );
    }
}
