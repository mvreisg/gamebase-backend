<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Factories\Controllers\HttpGamePlatformControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Factories\Entities\HttpRouteFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Factories\Entities\HttpRoutePartFactory;

class HttpGamePlatformRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpGamePlatformControllerFactory::make();

            $routes = [
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Post
                    )
                    ->setParts(
                        [
                            HttpRoutePartFactory::make(
                                'game',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'platform',
                                HttpRouteParameterTypesEnum::Route
                            ),
                        ]
                    )
                    ->setCallback(
                        fn ($request, $response) => $controller->insert($request, $response)
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Put
                    )
                    ->setParts(
                        [
                            HttpRoutePartFactory::make(
                                'game',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'platform',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'id',
                                HttpRouteParameterTypesEnum::Integer
                            )
                        ]
                    )
                    ->setCallback(
                        fn ($request, $response) => $controller->update($request, $response)
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Delete
                    )
                    ->setParts(
                        [
                            HttpRoutePartFactory::make(
                                'game',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'platform',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'id',
                                HttpRouteParameterTypesEnum::Integer
                            )
                        ]
                    )
                    ->setCallback(
                        fn ($request, $response) => $controller->delete($request, $response)
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Get
                    )
                    ->setParts(
                        [
                            HttpRoutePartFactory::make(
                                'game',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'platform',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'id',
                                HttpRouteParameterTypesEnum::Integer
                            )
                        ]
                    )
                    ->setCallback(
                        fn ($request, $response) => $controller->findById($request, $response)
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Get
                    )
                    ->setParts(
                        [
                            HttpRoutePartFactory::make(
                                'game',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'platform',
                                HttpRouteParameterTypesEnum::Route
                            )
                        ]
                    )
                    ->setCallback(
                        fn ($request, $response) => $controller->findAll($request, $response)
                    )
            ];

            return $routes;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
