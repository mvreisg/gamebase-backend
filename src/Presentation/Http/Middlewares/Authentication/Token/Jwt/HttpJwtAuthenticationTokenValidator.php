<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Validation\AuthenticationValidationResult;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpHeader;

class HttpJwtAuthenticationTokenValidator
{
    public static function validate(
        HttpHeader $header,
        AuthenticationService $authenticationService
    ): AuthenticationValidationResult {
        $pieces = explode(" ", $header->getValue());
        $tokenAsString = trim($pieces[1]);
        return $authenticationService->validateToken(
            new EncodedAuthenticationToken(
                $tokenAsString
            )
        );
    }
}
