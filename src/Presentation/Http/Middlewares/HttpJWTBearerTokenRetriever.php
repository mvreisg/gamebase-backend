<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Middlewares;

use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUnauthorizedException;

class HttpJWTBearerTokenRetriever
{
    public static function retrieveFromHeaders(array $headers): string
    {
        try {
            if (isset($headers['Authorization']) === false) {
                throw new HttpUnauthorizedException(
                    'You must inform the authentication token!'
                );
            }
            $bearer = $headers['Authorization'];
            $bearer = trim($bearer);
            $exploded = explode(' ', $bearer);
            $token = $exploded[1];
            return $token;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
