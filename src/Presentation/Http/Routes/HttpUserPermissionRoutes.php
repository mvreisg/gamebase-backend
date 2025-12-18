<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories\HttpUserPermissionControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRouteFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRoutePartFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Controllers\HttpControllerHandler;

class HttpUserPermissionRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpUserPermissionControllerFactory::make();

            $routes = [
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Post
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "user",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypesEnum::Route
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
                        HttpMethodTypesEnum::Put
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "user",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "id",
                            HttpRouteParameterTypesEnum::Integer
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
                        HttpMethodTypesEnum::Delete
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "user",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "id",
                            HttpRouteParameterTypesEnum::Integer
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
                        HttpMethodTypesEnum::Get
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "user",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "id",
                            HttpRouteParameterTypesEnum::Integer
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
                        HttpMethodTypesEnum::Get
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "user",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypesEnum::Route
                        )
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
