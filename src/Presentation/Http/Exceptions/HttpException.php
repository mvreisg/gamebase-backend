<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Exceptions;

class HttpException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct("HTTP exception: $message");
    }
}
