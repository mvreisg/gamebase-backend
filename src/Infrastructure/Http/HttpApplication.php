<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

/**
 * The HTTP Application class.
 * Manages all the HTTP routes and methods.
 */
class HttpApplication
{
    /**
     * @var List<int,string> STATUS_CODES The status codes list.
     */
    public const STATUS_CODES = [
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        204 => 'HTTP/1.1 204 No Content',
        400 => 'HTTP/1.1 400 Bad Request',
        404 => 'HTTP/1.1 404 Not Found',
        500 => 'HTTP/1.1 500 Internal Server Error'
    ];

    /**
     * @var List<string,string> HEADERS The headers list.
     */
    public const HEADERS = [
        'CONTENT_TYPE_APPLICATION_JSON' => 'Content-Type: application/json'
    ];

    /**
     * @var string NON_EXISTANT_ROUTE Value for a non-existant route. Needed by this class to send a 404 status if a route connot be found.
     */
    public const NON_EXISTANT_ROUTE = '';

    /**
     * @var List<string,string,callable> $routes The list of routes.
     */
    private array $routes = [];

    /**
     * Adds a route with a method and a callback function.
     * @param string $method The HTTP method (GET, POST, PUT, etc...)
     * @param string $route The name of the route, for example: ("/game")
     * @param callable $callback The callback function that handles the HTTP action (request and response).
     * @return void
     */
    public function add(string $method, string $route, callable $callback)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $route,
            'callback' => $callback
        ];
    }

    /**
     * Method that runs the HTTP application and keeps listening any new HTTP requests.
     * @return void
     */
    public function run()
    {
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

        $nonExistantRoute = null;
        foreach ($this->routes as $item) {
            if (($item['method'] === $method || $item['method'] === '*') && $this->matchRoute($item['route'], $routePart)) {
                $params = $this->findRouteParameters($item['route'], $routePart);
                $queries = $containsQueryParameters ? $this->findQueryParameters($queryPart) : [];
                $request = new HttpRequest($method, $routePart, $queries, $params, $body);
                $response = new HttpResponse();
                call_user_func_array($item['callback'], [$request, $response]);
                return;
            }
            if ($item['route'] === self::NON_EXISTANT_ROUTE) {
                $nonExistantRoute = $item;
            }
        }

        $request = new HttpRequest($method, $routePart);
        $response = new HttpResponse();
        call_user_func_array($nonExistantRoute['callback'], [$request, $response]);
    }

    /**
     * Method that gets two route names (the internal route defined in the code (the request route) and the actual requested route (the informed route)) and see if they match.
     * @return bool Returns true if the values match, false otherwise.
     */
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

    /**
     * Method that receives the path part all the query parameters and returns only a map of it.
     * @param string $path The path which will de extracted the values.
     * @return Map<string,string> The map of query parameters.
     */
    private function findQueryParameters(string $path)
    {
        $queries = [];

        $explodedTuples = explode('&', $path);

        foreach ($explodedTuples as $tuple) {
            $list = explode('=', $tuple);
            $queries[$list[0]] = $list[1];
        }

        return $queries;
    }

    /**
     * Method that gets the internal route defined in the code and the actual requested HTTP route and matches them to extract the parameters values from the actual requested route.
     * @return Map<string,string> The map of parameters.
     */
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
                $params[$key] = $informedWord;
            }
        }

        return $params;
    }
}
