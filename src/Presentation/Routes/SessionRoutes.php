<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\SessionControllerFactory;

class SessionRoutes
{
    /**
     * Registers the routes relatives to this entity in the router.
     * @param HttpRouter $app The HTTP router
     * @return void
     */
    public function register(HttpRouter $app)
    {
        $controller = SessionControllerFactory::get();

        $app->add('GET', '/session/start/:userId', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->handleSessionStart($request, $response);
        });

        $app->add('POST', '/session/validate', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->handleSessionValidation($request, $response);
        });
    }
}
