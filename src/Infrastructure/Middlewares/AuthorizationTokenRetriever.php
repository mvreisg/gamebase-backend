<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Middlewares;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Throwable;

class AuthorizationTokenRetriever
{
    public static function getFromHeaders(array $headers): string
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
