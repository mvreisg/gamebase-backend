<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;

class UserNameRetriever
{
    public static function getFromHeaders(array $headers): string
    {
        try {
            if (isset($headers['Username']) === false) {
                throw new AuthenticationException('É necessário informar o usuário!');
            }
            $value = $headers['Username'];
            return $value;
        } catch (AuthenticationException $e) {
            throw $e;
        }
    }
}
