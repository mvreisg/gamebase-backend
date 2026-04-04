<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Authentication\Exception;

class UnexistantTokenException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            "Unexistant token."
        );
    }
}
