<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Router;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoute;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpInvalidParameterException;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodeTypesEnum;

class HttpRouter
{
    private array $headers;
    private array $routes = [];

    public function __construct()
    {
        $this->headers = [
            'Access-Control-Allow-Methods: POST, GET, PATCH, DELETE, PUT',
            'Access-Control-Allow-Headers: Content-Type, Authorization',
            'Access-Control-Allow-Credentials: true',
        ];

        $separator = DotenvEnvironment::get('API_CONSUMERS_ADDRESSES_SEPARATOR');
        $origins = DotenvEnvironment::getArray('API_CONSUMERS_ADDRESSES', $separator);

        foreach ($origins as $origin) {
            $this->headers[] = 'Access-Control-Allow-Origin: ' . $origin;
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

            $path = $_SERVER['REQUEST_URI'];

            $method = $_SERVER['REQUEST_METHOD'];

            $explodedPath = explode('?', $path);

            $routePart = $explodedPath[0];

            $containsQueryParameters = count($explodedPath) > 1;
            $queryPart = null;
            if ($containsQueryParameters) {
                $queryPart = $explodedPath[1];
            }

            $body = file_get_contents('php://input');
            $headers = getallheaders();

            if ($method === 'OPTIONS') {
                header(HttpStatusCodeTypesEnum::NoContent->value);
                return;
            }

            $tokenizedRoute = explode('/', $routePart);
            array_shift($tokenizedRoute);
            $tokenizedRouteCount = count($tokenizedRoute);

            $filteredRoutes = array_filter(
                $this->routes,
                fn ($item) => $item->getMethod()->value === $method && $item->getPartsCount() === $tokenizedRouteCount
            );

            foreach ($filteredRoutes as $route) {
                $routePartsCount = $route->getPartsCount();
                $isThisRoute = true;
                for ($i = 0; $i < $routePartsCount; $i++) {
                    $params = [];
                    $isRoutePart = false;
                    $routePart = $route->getPart($i);
                    $routePartName = $routePart->getName();
                    $routePartType = $routePart->getType();
                    $routePartValue = $tokenizedRoute[$i];
                    switch ($routePartType) {
                        case HttpRouteParameterTypesEnum::Route:
                            if ($routePartValue === $routePartName) {
                                $isRoutePart = true;
                            } else {
                                $isThisRoute = false;
                            }
                            break;
                        case HttpRouteParameterTypesEnum::Text:
                            $isMatchingValue =
                                is_string($routePartValue) &&
                                $routePartValue !== "true" &&
                                $routePartValue !== "false";
                            $isThisRoute = $isMatchingValue;
                            break;
                        case HttpRouteParameterTypesEnum::Integer:
                            $isMatchingValue = filter_var($routePartValue, FILTER_VALIDATE_INT);
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
                            throw new HttpInvalidParameterException('O valor ' . $routePartValue . ' é inválido!');
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
                    $queriesMap = explode('&', $queryPart);
                    $queriesMap = array_map(
                        fn ($item) => explode('=', $item),
                        $queriesMap
                    );
                    foreach ($queriesMap as $key => $value) {
                        $queries[$value[0]] = $value[1];
                    }
                }

                $request = new HttpRequest($method, $route, $queries, $params, $body, $headers);
                $response = new HttpResponse();

                call_user_func_array($route->getCallback(), [$request, $response]);
                return;
            }

            header(HttpStatusCodeTypesEnum::NotFound->value);
            print('Route not found!');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
