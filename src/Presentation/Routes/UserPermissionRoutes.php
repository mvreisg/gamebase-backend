<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Routes;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Factories\UserPermissionControllerFactory;

class UserPermissionRoutes
{
    public function register(HttpRouter $app): void
    {
        $controller = UserPermissionControllerFactory::make();

        $app->add('POST', '/user/permission', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->insert($request, $response);
        });

        $app->add('PUT', '/user/permission/:id', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->update($request, $response);
        });

        $app->add('DELETE', '/user/permission/:id', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->delete($request, $response);
        });

        $app->add('GET', '/user/permission/:id', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->findById($request, $response);
        });

        $app->add('GET', '/user/permission', function (
            HttpRequest $request,
            HttpResponse $response
        ) use ($controller) {
            $controller->findAll($request, $response);
        });
    }
}
