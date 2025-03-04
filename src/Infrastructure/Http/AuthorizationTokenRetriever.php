<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;

class AuthorizationTokenRetriever
{
    public static function getFromHeaders(array $headers): string
    {
        try {
            if (isset($headers['Authorization']) === false) {
                throw new AuthenticationException('É necessário informar o token de autenticação!');
            }
            $bearer = $headers['Authorization'];
            $token = substr($bearer, 7);
            return $token;
        } catch (AuthenticationException $e) {
            throw $e;
        }
    }
}
