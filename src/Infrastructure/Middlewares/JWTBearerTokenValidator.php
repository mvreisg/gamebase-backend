<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Middlewares;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Throwable;

class JWTBearerTokenValidator
{
    private string $token;

    public static function make(string $token): JWTBearerTokenValidator
    {
        return new JWTBearerTokenValidator($token);
    }

    private function __construct(string $token)
    {
        $this->token = $token;
    }

    public function validate(AuthenticationService $authenticationService): void
    {
        try {
            if ($this->token === '') {
                throw new AuthenticationException('Token não informado!');
            }
            $isAuthenticated = $authenticationService->validateToken($this->token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }
        } catch (
            Throwable |
            AuthenticationException $e
        ) {
            throw $e;
        }
    }
}
