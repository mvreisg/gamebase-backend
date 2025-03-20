<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

class HttpRouter
{
    public const STATUS_CODES = [
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        204 => 'HTTP/1.1 204 No Content',
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        500 => 'HTTP/1.1 500 Internal Server Error'
    ];

    public const HEADERS = [
        'CONTENT_TYPE_APPLICATION_JSON' => 'Content-Type: application/json',
        'ACCESS_CONTROL_ALLOW_ORIGIN' => 'Access-Control-Allow-Origin: http://localhost:8081',
        'ACCESS_CONTROL_ALLOW_METHODS' => 'Access-Control-Allow-Methods: POST, GET, PATCH, DELETE, PUT',
        'ACCESS_CONTROL_ALLOW_HEADERS' => 'Access-Control-Allow-Headers: Content-Type, Authorization',
        'ACCESS_CONTROL_ALLOW_CREDENTIALS' => 'Access-Control-Allow-Credentials: true',
    ];

    public const NON_EXISTANT_ROUTE = '';

    public const WILDCARD_METHOD = '*';

    private array $routes = [];

    public function add(string $method, string $route, callable $callback)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $route,
            'callback' => $callback
        ];
    }

    public function run()
    {
        header(HttpRouter::HEADERS['ACCESS_CONTROL_ALLOW_ORIGIN']);
        header(HttpRouter::HEADERS['ACCESS_CONTROL_ALLOW_METHODS']);
        header(HttpRouter::HEADERS['ACCESS_CONTROL_ALLOW_HEADERS']);
        header(HttpRouter::HEADERS['ACCESS_CONTROL_ALLOW_CREDENTIALS']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }

        $path = $_SERVER['REQUEST_URI'];

        $method = $_SERVER['REQUEST_METHOD'];

        // /game/1 ? a=1&b=2
        $explodedPath = explode('?', $path);
        // -> [/game/1] ? a=1&b=2
        $routePart = $explodedPath[0];
        $containsQueryParameters = count($explodedPath) > 1;
        $queryPart = null;
        if ($containsQueryParameters) {
            // /game/1 ? -> [a=1&b=2]
            $queryPart = $explodedPath[1];
        }
        $body = file_get_contents('php://input');
        $headers = getallheaders();

        $matchingMethods = array_filter(
            $this->routes,
            fn ($item) => $item['method'] === $method || $item['method'] === self::WILDCARD_METHOD
        );

        $exactlyMatchedRoutes = array_filter($matchingMethods, fn ($item) => $item['route'] === $routePart);
        $looselyMatchedRoutes = array_filter(
            $matchingMethods,
            fn ($item) => $this->matchRoute($item['route'], $routePart)
        );

        $numberOfExactlyMatchedRoutes = count($exactlyMatchedRoutes);
        $numberOfLooselyMatchedRoutes = count($looselyMatchedRoutes);

        if ($numberOfExactlyMatchedRoutes === 1) {
            $item = array_pop($exactlyMatchedRoutes);
            $queries = $containsQueryParameters ? $this->findQueryParameters($queryPart) : [];
            $request = new HttpRequest($method, $routePart, $queries, [], $body, $headers);
            $response = new HttpResponse();
            call_user_func_array($item['callback'], [$request, $response]);
            return;
        } elseif ($numberOfLooselyMatchedRoutes === 1) {
            $item = array_pop($looselyMatchedRoutes);
            $params = $this->findRouteParameters($item['route'], $routePart);
            $queries = $containsQueryParameters ? $this->findQueryParameters($queryPart) : [];
            $request = new HttpRequest($method, $routePart, $queries, $params, $body, $headers);
            $response = new HttpResponse();
            call_user_func_array($item['callback'], [$request, $response]);
            return;
        } else {
            header(self::STATUS_CODES[404]);
            print('Rota não encontrada!');
        }
    }

    private function matchRoute(string $requestRoute, string $informedRoute)
    {
        if ($requestRoute === $informedRoute) {
            return true;
        }

        $explodedRequestRoute = explode('/', $requestRoute);
        $explodedInformedRequestRoute = explode('/', $informedRoute);
        array_shift($explodedRequestRoute);
        array_shift($explodedInformedRequestRoute);

        $doTheyHaveTheSameSize = count($explodedRequestRoute) === count($explodedInformedRequestRoute);
        if ($doTheyHaveTheSameSize === false) {
            return false;
        }

        for ($i = 0; $i < count($explodedRequestRoute); $i++) {
            $requestWord = $explodedRequestRoute[$i];
            if ($requestWord === '') {
                return false;
            }

            $informedWord = $explodedInformedRequestRoute[$i];

            $maybeItIsRouteParameter = false;
            if ($requestWord !== $informedWord) {
                $maybeItIsRouteParameter = true;
            } else {
                continue;
            }

            $matches = false;
            if ($maybeItIsRouteParameter) {
                $matches = preg_match('/:([A-Za-z0-9-_]+)/', $requestWord);
            }

            if ($matches == false) {
                return false;
            }
        }

        return true;
    }

    private function findQueryParameters(string $path)
    {
        $queries = [];

        $explodedTuples = explode('&', $path);

        foreach ($explodedTuples as $tuple) {
            $list = explode('=', $tuple);
            $key = $list[0];
            $value = $list[1];
            if (ctype_digit($value) && is_numeric($value)) {
                $value = intval($value);
            } elseif (is_numeric($value)) {
                $value = floatval($value);
            }
            $queries[$key] = $value;
        }

        return $queries;
    }

    private function findRouteParameters(string $requestRoute, string $informedRoute)
    {
        $params = [];

        $explodedRequestRoute = explode('/', $requestRoute);
        $explodedInformedRequestRoute = explode('/', $informedRoute);

        for ($i = 0; $i < count($explodedRequestRoute); $i++) {
            $requestWord = $explodedRequestRoute[$i];
            $informedWord = $explodedInformedRequestRoute[$i];

            $maybeItIsRouteParameter = false;
            if ($requestWord !== $informedWord) {
                $maybeItIsRouteParameter = true;
            } else {
                continue;
            }

            $matches = false;
            if ($maybeItIsRouteParameter) {
                $matches = preg_match('/:([A-Za-z0-9-_]+)/', $requestWord);
            }

            $isValueEmpty = false;
            if ($matches) {
                $key = str_replace(':', '', $requestWord);
                $isValueEmpty = $informedWord === '';
            }

            if ($isValueEmpty === false) {
                $value = $informedWord;
                if (ctype_digit($value) && is_numeric($value)) {
                    $value = intval($value);
                } elseif (is_numeric($value)) {
                    $value = floatval($value);
                }
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
