<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Middlewares;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Throwable;

class JWTBearerTokenRetriever
{
    public static function make(): JWTBearerTokenRetriever
    {
        return new JWTBearerTokenRetriever();
    }

    private function __construct()
    {
    }

    public function retrieveFromHeaders(array $headers): string
    {
        try {
            if (isset($headers['Authorization']) === false) {
                throw new AuthenticationException('É necessário informar o token de autenticação!');
            }
            $bearer = $headers['Authorization'];
            $bearer = trim($bearer);
            $exploded = explode(' ', $bearer);
            $token = $exploded[1];
            return $token;
        } catch (
            Throwable |
            AuthenticationException $e
        ) {
            throw $e;
        }
    }
}
