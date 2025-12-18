<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt;

use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpUnauthorizedException;

class HttpJwtAuthenticationTokenRetriever
{
    public static function retrieve(string $header): string
    {
        try {
            $bearer = trim($header);
            $exploded = explode(' ', $bearer);
            if (isset($exploded[1]) === false) {
                throw new HttpUnauthorizedException(
                    'Authentication token not informed!'
                );
            }
            $token = $exploded[1];
            return $token;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
