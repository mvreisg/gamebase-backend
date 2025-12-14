<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories\HttpGameControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRouteFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRoutePartFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Controllers\HttpControllerHandler;

class HttpGameRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpGameControllerFactory::make();

            $routes = [
                HttpRouteFactory::make()
                    ->setSeparator('/')
                    ->setMethod(
                        HttpMethodTypesEnum::Post
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            'game',
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
                    ->setSeparator('/')
                    ->setMethod(
                        HttpMethodTypesEnum::Put
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            'game',
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            'id',
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
                    ->setSeparator('/')
                    ->setMethod(
                        HttpMethodTypesEnum::Patch
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            'game',
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            'id',
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
                    ->setSeparator('/')
                    ->setMethod(
                        HttpMethodTypesEnum::Get
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            'game',
                            HttpRouteParameterTypesEnum::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            'id',
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
                    ->setSeparator('/')
                    ->setMethod(
                        HttpMethodTypesEnum::Get
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            'game',
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
