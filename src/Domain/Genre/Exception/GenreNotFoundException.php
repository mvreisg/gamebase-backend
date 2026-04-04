<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Genre\Exception;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GenreNotFoundException extends \Exception
{
    public function __construct(Id $id)
    {
        parent::__construct(
            "The genre with id '{$id->getValue()}' was not found."
        );
    }
}
