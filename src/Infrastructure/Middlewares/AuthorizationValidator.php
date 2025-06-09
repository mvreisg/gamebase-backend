<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Middlewares;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Throwable;

class AuthorizationValidator
{
    private string $token = '';

    public static function make()
    {
        return new AuthorizationValidator();
    }

    public function setToken(array $headers): AuthorizationValidator
    {
        try {
            if (isset($headers['Authorization']) === false) {
                throw new AuthenticationException('É necessário informar o token de autenticação!');
            }
            $bearer = $headers['Authorization'];
            $bearer = trim($bearer);
            $exploded = explode(' ', $bearer);
            $token = $exploded[1];
            $this->token = $token;
        } catch (
            Throwable |
            AuthenticationException $e
        ) {
            throw $e;
        } finally {
            return $this;
        }
    }

    public function getToken()
    {
        return $this->token;
    }

    public function validate(AuthenticationService $authenticationService)
    {
        if ($this->token === '') {
            throw new AuthenticationException('Token não informado!');
        }
        $isAuthenticated = $authenticationService->validateToken($this->token);
        if ($isAuthenticated === false) {
            throw new AuthenticationException('Usuário não autenticado!');
        }
        return $this;
    }
}
