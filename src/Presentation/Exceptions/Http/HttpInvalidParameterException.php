<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Exceptions\Http;

use Mvreisg\GamebaseBackend\Presentation\Exceptions\Enums\PresentationExceptionTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\PresentationException;

class HttpInvalidParameterException extends PresentationException
{
    public function __construct(string $message, \Throwable|null $cause = null)
    {
        parent::__construct(
            $message,
            PresentationExceptionTypesEnum::Http,
            $cause
        );
    }
}
