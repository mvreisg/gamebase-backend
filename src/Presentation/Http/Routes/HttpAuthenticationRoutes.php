<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Routes;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethods;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Controllers\Factories\HttpAuthenticationControllerFactory;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoute;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoutePart;

class HttpAuthenticationRoutes
{
    public static function get(): array
    {
        try {
            $controller = HttpAuthenticationControllerFactory::make();

            $routes = [
                HttpRoute::make()
                    ->setSeparator("/")
                    ->setMethod(
                        HttpMethods::Post
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "auth",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "login",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->handleLogin($request)
                    ),
                HttpRoute::make()
                    ->setSeparator("/")
                    ->setMethod(
                        HttpMethods::Get
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "auth",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "validate",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->handleValidation($request)
                    ),
                HttpRoute::make()
                    ->setSeparator("/")
                    ->setMethod(
                        HttpMethods::Delete
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "auth",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->appendPathPart(
                        HttpRoutePart::make(
                            "logoff",
                            HttpRouteParameterTypes::Route
                        )
                    )
                    ->setCallback(
                        fn ($request) => $controller->handleLogoff($request)
                    )
            ];

            return $routes;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
