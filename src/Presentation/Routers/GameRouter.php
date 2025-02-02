<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routers;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
use Mvreisg\GamebaseBackend\Presentation\Factories\GameControllerFactory;

/**
 * Game router class.
 * Manages the game HTTP routes.
 */
class GameRouter
{
    /**
     * Method that registers the router in the HTTP application
     * @param HttpApplication $app The application to add this router.
     * @return void
     */
    public function register(HttpApplication $app)
    {
        $controller = GameControllerFactory::get();

        $app->add('POST', '/game', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('GET', '/game', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAll($request, $response);
        });

        $app->add('GET', '/game/:gameId', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findById($request, $response);
        });

        $app->add('PUT', '/game/:gameId', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->update($request, $response);
        });
    }
}
