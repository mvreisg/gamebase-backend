<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\Exception;

class NullPasswordException extends \Exception
{
    public function __construct()
    {
        parent::__construct("The password of the game is null.");
    }
}
