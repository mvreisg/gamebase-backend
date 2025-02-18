<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\GameControllerFactory;

/**
 * Game routes class.
 */
class GameRoutes
{
    /**
     * Registers the routes relatives to this entity in the router.
     * @param HttpRouter $app The HTTP router
     * @return void
     */
    public function register(HttpRouter $app)
    {
        $controller = GameControllerFactory::get();

        $app->add('POST', '/game', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('PUT', '/game/:gameId', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->update($request, $response);
        });

        $app->add('GET', '/game', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAll($request, $response);
        });

        $app->add('GET', '/game/:gameId', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findById($request, $response);
        });
    }
}
