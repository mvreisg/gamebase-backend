<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethods;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories\HttpGameControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRouteFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\Factories\HttpRoutePartFactory;

class HttpGameRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpGameControllerFactory::make();

            $routes = [
                HttpRouteFactory::make()
                    ->setSeparator("/")
                    ->setMethod(
                        HttpMethods::Post
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "game",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->insert($request)
                    ),
                HttpRouteFactory::make()
                    ->setSeparator("/")
                    ->setMethod(
                        HttpMethods::Put
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "game",
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
                    ->setSeparator("/")
                    ->setMethod(
                        HttpMethods::Patch
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "game",
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
                        fn ($request) => $controller->setIsActive($request)
                    ),
                HttpRouteFactory::make()
                    ->setSeparator("/")
                    ->setMethod(
                        HttpMethods::Get
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "game",
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
                    ->setSeparator("/")
                    ->setMethod(
                        HttpMethods::Get
                    )
                    ->appendPathPart(
                        HttpRoutePartFactory::make(
                            "game",
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
