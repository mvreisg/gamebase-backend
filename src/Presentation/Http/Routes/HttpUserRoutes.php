<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Factories\Controllers\HttpUserControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Factories\Entities\HttpRouteFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Factories\Entities\HttpRoutePartFactory;

class HttpUserRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpUserControllerFactory::make();

            $routes = [
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Post
                    )
                    ->setParts(
                        [
                            HttpRoutePartFactory::make(
                                'user',
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
                                'user',
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
                                'user',
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
                                'user',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'id',
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
                                'user',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'username',
                                HttpRouteParameterTypesEnum::Route
                            ),
                            HttpRoutePartFactory::make(
                                'username',
                                HttpRouteParameterTypesEnum::Text
                            )
                        ]
                    )
                    ->setCallback(
                        fn ($request, $response) => $controller->findByUserName($request, $response)
                    ),
                HttpRouteFactory::make()
                    ->setMethod(
                        HttpMethodTypesEnum::Get
                    )
                    ->setParts(
                        [
                            HttpRoutePartFactory::make(
                                'user',
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
