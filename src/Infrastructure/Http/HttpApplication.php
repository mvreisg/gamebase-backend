<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

/**
 * The HTTP Application class.
 * Manages all the HTTP routes and methods.
 */
class HttpApplication
{
    /**
     * @var list<int,string> STATUS_CODES The status codes list.
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
     * @var list<string,string> HEADERS The headers list.
     */
    public const HEADERS = [
        'CONTENT_TYPE_APPLICATION_JSON' => 'Content-Type: application/json'
    ];

    /**
     * @var string NON_EXISTANT_ROUTE Value for a non-existant route. Needed by this class to send a 404 status if a route connot be found.
     */
    public const NON_EXISTANT_ROUTE = '';

    /**
     * @var list<string,string,callable> $routes The list of routes.
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
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];
        $route = explode('?', $path)[0];
        $explodedPath = explode('/', $path);
        array_shift($explodedPath);
        $rawQueries = [];
        $queries = [];
        $explodedTuples = [];
        $body = file_get_contents('php://input');
        $params = [];

        foreach ($explodedPath as $pathSegment) {
            //b?c=1&d=2
            $explodedQuery = explode('?', $pathSegment);
            //[0] b
            //[1] c=1&d=2
            if (count($explodedQuery) > 1) {
                $rawQueries[] = $explodedQuery[1];
            }
        }

        foreach ($rawQueries as $query) {
            $explodedTuples = explode('&', $query);
        }

        foreach ($explodedTuples as $tuple) {
            $keyValue = explode('=', $tuple);
            $queries[$keyValue[0]] = $keyValue[1];
        }

        $numberOfTries = 0;
        foreach ($this->routes as $singleRoute) {
            if ($singleRoute['route'] === HttpApplication::NON_EXISTANT_ROUTE) {
                $numberOfTries++;
                continue;
            }

            if (strtoupper($singleRoute['method']) === strtoupper($method) && $this->matchRoute($singleRoute['route'], $route)) {
                $params = $this->findRouteParams($singleRoute['route'], $route);
                $request = new HttpRequest($method, $route, $queries, $params, $body);
                $response = new HttpResponse();
                call_user_func_array($singleRoute['callback'], [$request, $response]);
                return;
            }

            $numberOfTries++;
        }

        if ($numberOfTries >= count($this->routes)) {
            foreach ($this->routes as $singleRoute) {
                if ($singleRoute['route'] === HttpApplication::NON_EXISTANT_ROUTE) {
                    $request = new HttpRequest($method, $route, $queries, $params, $body);
                    $response = new HttpResponse();
                    call_user_func_array($singleRoute['callback'], [$request, $response]);
                    return;
                }
            }
        }
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
     * Method that gets the internal route defined in the code and the actual requested HTTP route and matches them to extract the parameters values from the actual requested route.
     * @return list<string,string> The list of parameters.
     */
    private function findRouteParams(string $requestRoute, string $informedRoute)
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
