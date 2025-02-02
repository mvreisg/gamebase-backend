<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routers;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
use Mvreisg\GamebaseBackend\Presentation\Factories\GameGenreControllerFactory;

class GameGenreRouter
{
    public function register(HttpApplication $app)
    {
        $controller = GameGenreControllerFactory::get();

        $app->add('POST', '/game/:gameId/genre', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('PUT', '/game/:gameId/genre', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->edit($request, $response);
        });

        $app->add('GET', '/game/:gameId/genre', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAllGenresIdByGameId($request, $response);
        });

        $app->add('DELETE', '/game/:gameId/genre', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->deleteAllGenresByGameId($request, $response);
        });
    }
}
