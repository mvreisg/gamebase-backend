<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\AuthenticationControllerFactory;

class AuthenticationRoutes
{
    public function register(HttpRouter $app): void
    {
        $controller = AuthenticationControllerFactory::make();

        $app->add('POST', '/auth/login', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->handleLogin($request, $response);
        });

        $app->add('GET', '/auth/validate', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->handleValidation($request, $response);
        });

        $app->add('POST', '/auth/logoff', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->handleLogoff($request, $response);
        });
    }
}
