<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\GamePlatformControllerFactory;

/**
 * Game Platform routes class.
 */
class GamePlatformRoutes
{
    /**
     * Method that registers the routes in the HTTP router.
     */
    public function register(HttpRouter $app)
    {
        $controller = GamePlatformControllerFactory::get();

        $app->add('POST', '/game/platform', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('PUT', '/game/platform/:id', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->update($request, $response);
        });

        $app->add('DELETE', '/game/platform/:id', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->delete($request, $response);
        });

        $app->add('GET', '/game/platform/:id', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->findById($request, $response);
        });

        $app->add('GET', '/game/platform', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->findAll($request, $response);
        });
    }
}
