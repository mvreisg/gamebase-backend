<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Router;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpHeader;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpQuery;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoute;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethods;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteQueryTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodes;

class HttpRouter
{
    /**
     * @var string[]
     */
    private static array $DEFAULT_HEADERS = [
        "Access-Control-Allow-Methods: POST, GET, PATCH, DELETE, PUT, OPTIONS",
        "Access-Control-Allow-Headers: Content-Type, Authorization",
        "Access-Control-Allow-Credentials: true"
    ];

    /**
     * @var HttpHeader[]
     */
    private array $headers;

    /**
     * @var HttpRoute[]
     */
    private array $routes;

    public function __construct()
    {
        $this->routes = [];
        $this->headers = [];

        foreach (self::$DEFAULT_HEADERS as $header) {
            $this->headers[] = new HttpHeader($header);
        }

        $separator = DotenvEnvironment::get("API_CONSUMERS_ADDRESSES_SEPARATOR");
        $origins = DotenvEnvironment::getArray("API_CONSUMERS_ADDRESSES", $separator);

        if (count($origins) > 0) {
            foreach ($origins as $origin) {
                $this->headers[] = new HttpHeader(null, "Access-Control-Allow-Origin", $origin);
            }
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

    /**
     * @param HttpHeader[] $headers
     */
    private function send(HttpStatusCodes $statusCode, ?string $message, ?array $headers): void
    {
        if (isset($headers)) {
            foreach ($headers as $header) {
                header($header->getFull());
            }
        }
        header($statusCode->value);
        if (isset($message)) {
            print($message);
        }
    }

    private function sendRaw(HttpStatusCodes $statusCode, ?string $message, ?array $headers): void
    {
        if (isset($headers)) {
            foreach ($headers as $header) {
                header($header->getFull());
            }
        }
        header($statusCode->value);
        if (isset($message)) {
            header("Content-Type: application/json; charset=utf-8");
            print(
                json_encode([
                    "message" => $message
                ])
            );
        }
    }

    private function rightRouteButWrongValue(string $route, string $expected, mixed $value)
    {
        $message = "";
        if ($value !== "0" && empty($value)) {
            $message = "Expected ($expected) value on route: $route, nothing received!";
        } else {
            $message = "Expected ($expected) value on route: $route, received ($value).";
        }
        $this->sendRaw(
            HttpStatusCodes::BadRequest,
            $message,
            null
        );
        return;
    }

    public function run(): void
    {
        try {
            foreach ($this->headers as $header) {
                header($header->getFull());
            }

            $path = $_SERVER["REQUEST_URI"];

            $method = $_SERVER["REQUEST_METHOD"];
            switch ($method) {
                case "POST":
                    $method = HttpMethods::Post;
                    break;
                case "GET":
                    $method = HttpMethods::Get;
                    break;
                case "PATCH":
                    $method = HttpMethods::Patch;
                    break;
                case "DELETE":
                    $method = HttpMethods::Delete;
                    break;
                case "PUT":
                    $method = HttpMethods::Put;
                    break;
                case "OPTIONS":
                    $method = HttpMethods::Options;
                    break;
                default:
                    $this->sendRaw(
                        HttpStatusCodes::InternalServerError,
                        "Unsupported HTTP method: $method",
                        null
                    );
                    return;
            }

            $explodedPath = explode("?", $path);

            $routePart = $explodedPath[0];

            $containsQueryParameters = count($explodedPath) > 1;
            $queryPart = null;
            if ($containsQueryParameters) {
                $queryPart = $explodedPath[1];
            }

            $body = file_get_contents("php://input");

            foreach (getallheaders() as $key => $value) {
                $this->headers[] = new HttpHeader(null, $key, $value);
            }

            if ($method === HttpMethods::Options) {
                header(HttpStatusCodes::NoContent->value);
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
                    $routePartValue = trim(urldecode($tokenizedRoute[$i]));
                    switch ($routePartType) {
                        case HttpRouteParameterTypes::Route:
                            $isRoutePart = true;
                            $isThisRoute = $routePartValue === $routePartName;
                            break;
                        case HttpRouteParameterTypes::Text:
                            $isMatchingValue =
                                is_string($routePartValue) &&
                                $routePartValue !== "true" &&
                                $routePartValue !== "false" &&
                                $routePartValue !== "0" &&
                                filter_var($routePartValue, FILTER_VALIDATE_INT) === false &&
                                filter_var($routePartValue, FILTER_VALIDATE_FLOAT) === false &&
                                filter_var($routePartValue, FILTER_VALIDATE_BOOL) === false;
                            $isThisRoute = $isMatchingValue;
                            if (($i + 1) >= $routePartsCount && $isMatchingValue === false) {
                                $this->rightRouteButWrongValue(
                                    $route->getFullRouteName(),
                                    "string",
                                    $routePartValue
                                );
                                return;
                            }
                            break;
                        case HttpRouteParameterTypes::Integer:
                            $isMatchingValue =
                                filter_var($routePartValue, FILTER_VALIDATE_INT) ||
                                $routePartValue === "0";
                            if ($isMatchingValue) {
                                $routePartValue = intval($routePartValue);
                            }
                            $isThisRoute = $isMatchingValue;
                            if (($i + 1) >= $routePartsCount && $isMatchingValue === false) {
                                $this->rightRouteButWrongValue(
                                    $route->getFullRouteName(),
                                    "integer",
                                    $routePartValue
                                );
                                return;
                            }
                            break;
                        case HttpRouteParameterTypes::Decimal:
                            $isMatchingValue = filter_var($routePartValue, FILTER_VALIDATE_FLOAT);
                            if ($isMatchingValue) {
                                $routePartValue = floatval($routePartValue);
                            }
                            $isThisRoute = $isMatchingValue;
                            if (($i + 1) >= $routePartsCount && $isMatchingValue === false) {
                                $this->rightRouteButWrongValue(
                                    $route->getFullRouteName(),
                                    "decimal",
                                    $routePartValue
                                );
                                return;
                            }
                            break;
                        case HttpRouteParameterTypes::Boolean:
                            $isMatchingValue = filter_var($routePartValue, FILTER_VALIDATE_BOOL);
                            if ($isMatchingValue) {
                                $routePartValue = boolval($routePartValue);
                            }
                            $isThisRoute = $isMatchingValue;
                            if (($i + 1) >= $routePartsCount && $isMatchingValue === false) {
                                $this->rightRouteButWrongValue(
                                    $route->getFullRouteName(),
                                    "boolean",
                                    $routePartValue
                                );
                                return;
                            }
                            break;
                        default:
                            $this->sendRaw(
                                HttpStatusCodes::InternalServerError,
                                "Untreated route type: $routePartType",
                                null
                            );
                            return;
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
                                HttpRouteQueryTypes::Boolean,
                                $check
                            );
                            continue;
                        }

                        $isInteger = filter_var($check, FILTER_VALIDATE_INT);
                        if ($isInteger) {
                            $check = intval($check);
                            $queries[$index] = new HttpQuery(
                                HttpRouteQueryTypes::Integer,
                                $check
                            );
                            continue;
                        }

                        $isFloat = filter_var($check, FILTER_VALIDATE_FLOAT);
                        if ($isFloat) {
                            $check = floatval($check);
                            $queries[$index] = new HttpQuery(
                                HttpRouteQueryTypes::Float,
                                $check
                            );
                            continue;
                        }

                        $queries[$index] = new HttpQuery(
                            HttpRouteQueryTypes::String,
                            $check
                        );
                    }
                }

                $request = new HttpRequest(
                    $method,
                    $route,
                    $queries,
                    $params,
                    $body,
                    $this->headers
                );
                $callback = $route->getCallback();
                $response = $callback($request);
                $this->send(
                    $response->getStatusCode(),
                    $response->hasReadableBody() ? $response->parseBody() : null,
                    $response->getHeaders()
                );
                return;
            }

            $this->sendRaw(
                HttpStatusCodes::NotFound,
                "Route not found!",
                null
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
