<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Factories\Controllers\HttpGenreControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Factories\Entities\HttpRouteFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Factories\Entities\HttpRoutePartFactory;

class HttpGenreRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpGenreControllerFactory::make();

            $routes = [
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Post
                    )
                    ->setParts(
                        [
                            HttpRoutePartFactory::make(
                                'genre',
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
                                'genre',
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
                        HttpMethodTypesEnum::Patch
                    )
                    ->setParts(
                        [
                            HttpRoutePartFactory::make(
                                'genre',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'id',
                                HttpRouteParameterTypesEnum::Integer
                            )
                        ]
                    )
                    ->setCallback(
                        fn ($request, $response) => $controller->setIsActive($request, $response)
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Get
                    )
                    ->setParts(
                        [
                            HttpRoutePartFactory::make(
                                'genre',
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
                                'genre',
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
