<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories\HttpPermissionControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRouteFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRoutePartFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Controllers\HttpControllerHandler;

class HttpPermissionRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpPermissionControllerFactory::make();

            $routes = [
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Post
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "permission",
                            HttpRouteParameterTypesEnum::Route
                        ),
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
                        HttpMethodTypesEnum::Patch
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
                                $controller->setIsActive(...)
                            )
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Get
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
