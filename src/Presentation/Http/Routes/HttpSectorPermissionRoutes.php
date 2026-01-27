<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethods;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories\HttpSectorPermissionControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRouteFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRoutePartFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Controllers\HttpControllerHandler;

class HttpSectorPermissionRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpSectorPermissionControllerFactory::make();

            $routes = [
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethods::Post
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "sector",
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
                        fn ($request, $response)
                            => HttpControllerHandler::use(
                                $request,
                                $response,
                                $controller->insert(...)
                            )
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethods::Put
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "sector",
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
                        fn ($request, $response)
                            => HttpControllerHandler::use(
                                $request,
                                $response,
                                $controller->update(...)
                            )
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethods::Delete
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "sector",
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
                        fn ($request, $response)
                            => HttpControllerHandler::use(
                                $request,
                                $response,
                                $controller->delete(...)
                            )
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethods::Get
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "sector",
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
                        fn ($request, $response)
                            => HttpControllerHandler::use(
                                $request,
                                $response,
                                $controller->findById(...)
                            )
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethods::Get
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "sector",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypes::Route
                        ),
                    )
                    ->setCallback(
                        fn ($request, $response)
                            => HttpControllerHandler::use(
                                $request,
                                $response,
                                $controller->findAll(...)
                            )
                    )
            ];

            return $routes;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
