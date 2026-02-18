<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethods;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories\HttpUserSectorPermissionControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoute;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoutePart;

class HttpUserSectorPermissionRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpUserSectorPermissionControllerFactory::make();

            $routes = [
                HttpRoute::make()
                    ->setMethod(
                        HttpMethods::Post
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "user_sector_permission",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->insert($request)
                    ),
                HttpRoute::make()
                    ->setMethod(
                        HttpMethods::Put
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "user_sector_permission",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "id",
                            HttpRouteParameterTypes::Integer
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->update($request)
                    ),
                HttpRoute::make()
                    ->setMethod(
                        HttpMethods::Delete
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "user_sector_permission",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "id",
                            HttpRouteParameterTypes::Integer
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->delete($request)
                    ),
                HttpRoute::make()
                    ->setMethod(
                        HttpMethods::Get
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "user_sector_permission",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "id",
                            HttpRouteParameterTypes::Integer
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->findById($request)
                    ),
                HttpRoute::make()
                    ->setMethod(
                        HttpMethods::Get
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "user_sector_permission",
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
