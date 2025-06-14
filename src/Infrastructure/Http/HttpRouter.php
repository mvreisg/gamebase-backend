<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

class HttpRouter
{
    public static array $STATUS_CODES = [
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        204 => 'HTTP/1.1 204 No Content',
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        500 => 'HTTP/1.1 500 Internal Server Error'
    ];

    public static array $CONTENT_TYPES = [
        'JSON' => 'Content-Type: application/json; charset=utf-8',
    ];

    public static string $NON_EXISTANT_ROUTE = '';
    public static string $WILDCARD_METHOD = '*';

    private array $headers;
    private array $routes = [];

    public function __construct()
    {
        $this->headers = [
            'ACCESS_CONTROL_ALLOW_ORIGIN' => 'Access-Control-Allow-Origin: ' . $_SERVER['FRONTEND_ADDRESS'],
            'ACCESS_CONTROL_ALLOW_METHODS' => 'Access-Control-Allow-Methods: POST, GET, PATCH, DELETE, PUT',
            'ACCESS_CONTROL_ALLOW_HEADERS' => 'Access-Control-Allow-Headers: Content-Type, Authorization',
            'ACCESS_CONTROL_ALLOW_CREDENTIALS' => 'Access-Control-Allow-Credentials: true',
        ];
    }

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
            header(self::$STATUS_CODES[204]);
            return;
        }

        $filteredRoutes = array_filter($this->routes, fn ($item) => $item['method'] === $method);

        foreach ($filteredRoutes as $route) {
            $routeTokens = explode('/', $route['route']);
            array_shift($routeTokens);

            $tokenizedRoute = explode('/', $routePart);
            array_shift($tokenizedRoute);

            if (count($routeTokens) !== count($tokenizedRoute)) {
                continue;
            }

            $looselyTheSameRoute = true;

            for ($i = 0; $i < count($routeTokens); $i++) {
                if (preg_match('/:[a-z0-9]+/i', $routeTokens[$i])) {
                    continue;
                }

                if ($routeTokens[$i] !== $tokenizedRoute[$i]) {
                    $looselyTheSameRoute = false;
                    break;
                }
            }

            if ($looselyTheSameRoute === false) {
                continue;
            }

            $paramParamMatchesArray = preg_grep('/:[a-z0-9]+/i', $routeTokens);
            $paramParamMatchesIndexesArray = array_keys(
                array_filter($paramParamMatchesArray)
            );

            $params = [];
            for ($i = 0; $i < count($paramParamMatchesIndexesArray); $i++) {
                $index = $paramParamMatchesIndexesArray[$i];
                $key = $routeTokens[$index];
                $key = str_replace(':', '', $key);
                $value = $tokenizedRoute[$index];
                if (is_numeric($value)) {
                    $isFloat = str_contains($value, '.') || str_contains(strtolower($value), 'e');
                    if ($isFloat) {
                        $value = floatval($value);
                    } else {
                        $value = intval($value);
                    }
                } elseif (is_string($value)) {
                    $temp = strtolower($value);
                    switch ($temp) {
                        case 'true':
                        case 'false':
                            $value = boolval($temp);
                            break;
                        default:
                            break;
                    }
                }
                $params[$key] = $value;
            }

            $queries = [];
            if ($containsQueryParameters) {
                //'a=1&b=2'
                $queriesDividedIntoKeyValues = explode('&', $queryPart);
                //'a=1', 'b=2'
                $queriesDividedIntoKeyValues = array_map(
                    fn ($item) => explode('=', $item),
                    $queriesDividedIntoKeyValues
                );
                // [[a, 1], [b, 2]]
                foreach ($queriesDividedIntoKeyValues as $key => $value) {
                    $queries[$value[0]] = $value[1];
                }
                // [[a] => [1], [b] => [2]]
            }

            $request = new HttpRequest($method, $routePart, $queries, $params, $body, $headers);
            $response = new HttpResponse();
            call_user_func_array($route['callback'], [$request, $response]);
            return;
        }

        header(self::$STATUS_CODES[404]);
        print('Rota não encontrada!');
    }
}
