<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethods;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories\HttpUserPermissionControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRouteFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRoutePartFactory;

class HttpUserPermissionRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpUserPermissionControllerFactory::make();

            $routes = [
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethods::Post
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "user",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->insert($request)
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethods::Put
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "user",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "id",
                            HttpRouteParameterTypes::Integer
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->update($request)
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethods::Delete
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "user",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "id",
                            HttpRouteParameterTypes::Integer
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->delete($request)
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethods::Get
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "user",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "id",
                            HttpRouteParameterTypes::Integer
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->findById($request)
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethods::Get
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "user",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->findAll($request)
                    )
            ];

            return $routes;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
