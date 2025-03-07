<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\GenreControllerFactory;

class GenreRoutes
{
    public function register(HttpRouter $app)
    {
        $controller = GenreControllerFactory::get();

        $app->add('POST', '/genre', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('GET', '/genre', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAll($request, $response);
        });

        $app->add('PATCH', '/genre/:genreId', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->setIsActive($request, $response);
        });

        $app->add('GET', '/genre/:genreId', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findById($request, $response);
        });

        $app->add('PUT', '/genre/:genreId', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->update($request, $response);
        });
    }
}
