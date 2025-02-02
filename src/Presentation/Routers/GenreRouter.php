<?php

namespace Mvreisg\GamebaseBackend\Presentation\Routers;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
use Mvreisg\GamebaseBackend\Presentation\Factories\GenreControllerFactory;

/**
 * Genre router class.
 */
class GenreRouter
{
    /**
     * Method that registers the routes into the HTTP application.
     * @var HttpApplication $app The HTTP application.
     * @return void
     */
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
            $controller->update($request, $response);
        });
    }
}
