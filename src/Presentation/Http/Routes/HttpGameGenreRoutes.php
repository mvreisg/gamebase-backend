<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories\HttpGameGenreControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRouteFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRoutePartFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Controllers\HttpControllerHandler;

class HttpGameGenreRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpGameGenreControllerFactory::make();

            $routes = [
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Post
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "game",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "genre",
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
                            "game",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "genre",
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
                            "game",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "genre",
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
                            "game",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "genre",
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
                            "game",
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "genre",
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
