<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routers;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
use Mvreisg\GamebaseBackend\Presentation\Factories\GenreControllerFactory;

class GenreRouter
{
    public function register(HttpApplication $app)
    {
        $controller = GenreControllerFactory::get();

        $app->add('POST', '/genre', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('GET', '/genre', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAll($request, $response);
        });

        $app->add('GET', '/genre/:genreId', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findById($request, $response);
        });

        $app->add('PUT', '/genre/:genreId', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->edit($request, $response);
        });
    }
}
