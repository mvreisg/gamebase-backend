<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Middlewares;

use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Middlewares\JWTBearerTokenRetriever;
use Mvreisg\GamebaseBackend\Infrastructure\Middlewares\JWTBearerTokenValidator;

class RouteAuthenticator
{
    private AuthenticationService $authenticationService;

    public static function make(
        AuthenticationService $authenticationService
    ): RouteAuthenticator {
        return new RouteAuthenticator($authenticationService);
    }

    private function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function validate(HttpRequest $request, HttpResponse $response): string
    {
        $headers = $request->getHeaders();
        $token = JWTBearerTokenRetriever::make()->retrieveFromHeaders($headers);
        JWTBearerTokenValidator::make($token)->validate($this->authenticationService);
        return $token;
    }
}
