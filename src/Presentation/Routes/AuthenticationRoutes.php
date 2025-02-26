<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\AuthenticationControllerFactory;

class AuthenticationRoutes
{
    /**
     * Registers the routes relatives to this entity in the router.
     * @param HttpRouter $app The HTTP router
     * @return void
     */
    public function register(HttpRouter $app)
    {
        $controller = AuthenticationControllerFactory::get();

        $app->add('POST', '/authenticate', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->handleAutenticationCheck($request, $response);
        });
    }
}
