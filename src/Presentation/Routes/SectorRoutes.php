<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\SectorControllerFactory;

class SectorRoutes
{
    public function register(HttpRouter $app): void
    {
        $controller = SectorControllerFactory::make();

        $app->add('POST', '/sector', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('GET', '/sector', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findAll($request, $response);
        });

        $app->add('PATCH', '/sector/:id', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->setIsActive($request, $response);
        });

        $app->add('GET', '/sector/:id', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->findById($request, $response);
        });

        $app->add('PUT', '/sector/:id', function (HttpRequest $request, HttpResponse $response) use ($controller) {
            $controller->update($request, $response);
        });
    }
}
