<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Router;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpQuery;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoute;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteQueryTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodeTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;

class HttpRouter
{
    private array $headers;
    private array $routes = [];

    public function __construct()
    {
        $this->headers = [
            "Access-Control-Allow-Methods: POST, GET, PATCH, DELETE, PUT, OPTIONS",
            "Access-Control-Allow-Headers: Content-Type, Authorization",
            "Access-Control-Allow-Credentials: true",
        ];

        $separator = DotenvEnvironment::get("API_CONSUMERS_ADDRESSES_SEPARATOR");
        $origins = DotenvEnvironment::getArray("API_CONSUMERS_ADDRESSES", $separator);

        foreach ($origins as $origin) {
            $this->headers[] = "Access-Control-Allow-Origin: " . $origin;
        }
    }

    private function addRoute(HttpRoute $route): HttpRouter
    {
        $this->routes[] = $route;
        return $this;
    }

    public function addRoutes(array $routes): HttpRouter
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
        return $this;
    }

    public function run(): void
    {
        try {
            foreach ($this->headers as $header) {
                header($header);
            }

            $path = $_SERVER["REQUEST_URI"];

            $method = $_SERVER["REQUEST_METHOD"];
            switch ($method) {
                case "POST":
                    $method = HttpMethodTypesEnum::Post;
                    break;
                case "GET":
                    $method = HttpMethodTypesEnum::Get;
                    break;
                case "PATCH":
                    $method = HttpMethodTypesEnum::Patch;
                    break;
                case "DELETE":
                    $method = HttpMethodTypesEnum::Delete;
                    break;
                case "PUT":
                    $method = HttpMethodTypesEnum::Put;
                    break;
                case "OPTIONS":
                    $method = HttpMethodTypesEnum::Options;
                    break;
                default:
                    throw new HttpBadRequestException(
                        "Unsupported HTTP method: $method"
                    );
            }

            $explodedPath = explode("?", $path);

            $routePart = $explodedPath[0];

            $containsQueryParameters = count($explodedPath) > 1;
            $queryPart = null;
            if ($containsQueryParameters) {
                $queryPart = $explodedPath[1];
            }

            $body = file_get_contents("php://input");
            $headers = getallheaders();

            if ($method === HttpMethodTypesEnum::Options) {
                header(HttpStatusCodeTypesEnum::NoContent->value);
                return;
            }

            $tokenizedRoute = explode("/", $routePart);
            array_shift($tokenizedRoute);
            $tokenizedRouteCount = count($tokenizedRoute);

            $filteredRoutes = array_filter(
                $this->routes,
                fn (HttpRoute $item) =>
                    $item->getMethod() === $method &&
                    $item->getPathPartsCount() === $tokenizedRouteCount
            );

            /**
             * @var HttpRoute $route
             */
            foreach ($filteredRoutes as $route) {
                $routePartsCount = $route->getPathPartsCount();
                $isThisRoute = true;
                for ($i = 0; $i < $routePartsCount; $i++) {
                    $params = [];
                    $isRoutePart = false;
                    $routePart = $route->getPathPart($i);
                    $routePartName = $routePart->getName();
                    $routePartType = $routePart->getType();
                    $routePartValue = $tokenizedRoute[$i];
                    switch ($routePartType) {
                        case HttpRouteParameterTypesEnum::Route:
                            $isRoutePart = $isThisRoute = $routePartValue === $routePartName;
                            break;
                        case HttpRouteParameterTypesEnum::Text:
                            $isMatchingValue =
                                is_string($routePartValue) &&
                                $routePartValue !== "true" &&
                                $routePartValue !== "false";
                            $isThisRoute = $isMatchingValue;
                            break;
                        case HttpRouteParameterTypesEnum::Integer:
                            $isMatchingValue =
                                filter_var($routePartValue, FILTER_VALIDATE_INT) ||
                                $routePartValue === "0";
                            if ($isMatchingValue) {
                                $routePartValue = intval($routePartValue);
                            }
                            $isThisRoute = $isMatchingValue;
                            break;
                        case HttpRouteParameterTypesEnum::Decimal:
                            $isMatchingValue = filter_var($routePartValue, FILTER_VALIDATE_FLOAT);
                            if ($isMatchingValue) {
                                $routePartValue = floatval($routePartValue);
                            }
                            $isThisRoute = $isMatchingValue;
                            break;
                        case HttpRouteParameterTypesEnum::Boolean:
                            $isMatchingValue = filter_var($routePartValue, FILTER_VALIDATE_BOOL);
                            if ($isMatchingValue) {
                                $routePartValue = boolval($routePartValue);
                            }
                            $isThisRoute = $isMatchingValue;
                            break;
                        default:
                            throw new HttpBadRequestException(
                                "Untreated route type: $routePartType"
                            );
                    }

                    if ($isThisRoute === false) {
                        break;
                    }

                    if ($isRoutePart) {
                        continue;
                    }

                    $params[$routePartName] = $routePartValue;
                }

                if ($isThisRoute === false) {
                    continue;
                }

                $queries = [];
                if ($containsQueryParameters) {
                    $queriesMap = explode("&", $queryPart);
                    $queriesMap = array_map(
                        fn ($item) => explode("=", $item),
                        $queriesMap
                    );
                    foreach ($queriesMap as $key => $value) {
                        $index = $value[0];
                        $check = $value[1];
                        $isBoolean = $check === "true" || $check === "false";
                        if ($isBoolean) {
                            $check = $check === "true" ? true : false;
                            $queries[$index] = new HttpQuery(
                                HttpRouteQueryTypesEnum::Boolean,
                                $check
                            );
                            continue;
                        }

                        $isInteger = filter_var($check, FILTER_VALIDATE_INT);
                        if ($isInteger) {
                            $check = intval($check);
                            $queries[$index] = new HttpQuery(
                                HttpRouteQueryTypesEnum::Integer,
                                $check
                            );
                            continue;
                        }

                        $isFloat = filter_var($check, FILTER_VALIDATE_FLOAT);
                        if ($isFloat) {
                            $check = floatval($check);
                            $queries[$index] = new HttpQuery(
                                HttpRouteQueryTypesEnum::Float,
                                $check
                            );
                            continue;
                        }

                        $queries[$index] = new HttpQuery(
                            HttpRouteQueryTypesEnum::String,
                            $check
                        );
                    }
                }

                $request = new HttpRequest($method, $route, $queries, $params, $body, $headers);
                $response = new HttpResponse();
                $callback = $route->getCallback();
                $callback($request, $response);
                return;
            }

            header(HttpStatusCodeTypesEnum::NotFound->value);
            print("Route not found!");
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
