<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\GameGenreControllerFactory;

/**
 * Game Genre routes class.
 */
class GameGenreRoutes
{
    /**
     * Registers the routes relatives to this entity in the router.
     * @param HttpRouter $app The HTTP router
     */
    public function register(HttpRouter $app)
    {
        $controller = GameGenreControllerFactory::get();

        $app->add('POST', '/game/genre', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('PUT', '/game/genre/:id', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->update($request, $response);
        });

        $app->add('DELETE', '/game/genre/:id', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->delete($request, $response);
        });

        $app->add('GET', '/game/genre/:id', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findById($request, $response);
        });

        $app->add('GET', '/game/genre', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAll($request, $response);
        });
    }
}
