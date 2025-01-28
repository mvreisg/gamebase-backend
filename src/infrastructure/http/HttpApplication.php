<?php
    namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

    define("HEADER_CONTENT_TYPE_APPLICATION_JSON", "Content-Type: application/json");

    define("HTTP_STATUS_CODE_200", "HTTP/1.1 200 OK");
    define("HTTP_STATUS_CODE_201", "HTTP/1.1 201 Created");
    define("HTTP_STATUS_CODE_204", "HTTP/1.1 204 No Content");
    define("HTTP_STATUS_CODE_400", "HTTP/1.1 400 Bad Request");
    define("HTTP_STATUS_CODE_404", "HTTP/1.1 404 Not Found");
    define("HTTP_STATUS_CODE_500", "HTTP/1.1 500 Internal Server Error");

    define("NON_EXISTANT_ROUTE", "");

    class HttpApplication 
    {        
        private array $routes = [];

        public function add(string $method, string $route, callable $callback)
        {
            $this->routes[] = [
                "method" => strtoupper($method),
                "route" => $route,
                "callback" => $callback
            ];
        }

        public function run()
        {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = $_SERVER['REQUEST_URI'];
            $route = explode("?", $path)[0];
            $explodedPath = explode("/", $path);
            array_shift($explodedPath);
            $rawQueries = [];
            $queries = [];
            $explodedTuples = [];
            $body = file_get_contents('php://input');
            $params = [];

            foreach ($explodedPath as $pathSegment)
            {
                //b?c=1&d=2
                $explodedQuery = explode("?", $pathSegment);
                //[0] b 
                //[1] c=1&d=2
                if (count($explodedQuery) > 1)
                    $rawQueries[] = $explodedQuery[1];
            }

            foreach($rawQueries as $query)
            {
                $explodedTuples = explode("&", $query);
            }

            foreach($explodedTuples as $tuple)
            {
                $keyValue = explode("=", $tuple);
                $queries[$keyValue[0]] = $keyValue[1];
            }

            $numberOfTries = 0;
            foreach ($this->routes as $singleRoute)
            {
                if ($singleRoute["route"] === NON_EXISTANT_ROUTE)
                {
                    $numberOfTries++;
                    continue;
                }

                if (strtoupper($singleRoute["method"]) === strtoupper($method) && $this->matchRoute($singleRoute["route"], $route, $params))
                {               
                    $params = $this->findRouteParams($singleRoute["route"], $route);     
                    $request = new HttpRequest($method, $route, $queries, $params, $body);
                    $response = new HttpResponse();
                    call_user_func_array($singleRoute["callback"], [$request, $response]);
                    return;
                }                    
                    
                $numberOfTries++;
            }

            if ($numberOfTries >= count($this->routes))
            {
                foreach ($this->routes as $singleRoute)
                {
                    if ($singleRoute["route"] === NON_EXISTANT_ROUTE){                        
                        $request = new HttpRequest($method, $route, $queries, $params, $body);
                        $response = new HttpResponse();
                        call_user_func_array($singleRoute["callback"], [$request, $response]);
                        return;
                    }
                }
            }
        }
        
        private function matchRoute(string $requestRoute, string $informedRoute)
        { 
            if ($requestRoute === $informedRoute)
            {
                return true;
            }
            
            $explodedRequestRoute = explode("/", $requestRoute);
            $explodedInformedRequestRoute = explode("/", $informedRoute);
            array_shift($explodedRequestRoute);
            array_shift($explodedInformedRequestRoute);

            $doTheyHaveTheSameSize = count($explodedRequestRoute) === count($explodedInformedRequestRoute);
            if ($doTheyHaveTheSameSize === false)
            {
                return false;
            }

            for ($i = 0; $i < count($explodedRequestRoute); $i++)
            {
                $requestWord = $explodedRequestRoute[$i];
                if ($requestWord === "") 
                {
                    return false;
                }

                $informedWord = $explodedInformedRequestRoute[$i];

                $maybeItIsRouteParameter = false;
                if ($requestWord !== $informedWord) 
                {
                    $maybeItIsRouteParameter = true;
                }
                else
                {
                    continue;
                }

                $matches = false;
                if ($maybeItIsRouteParameter) 
                {
                    $matches = preg_match("/:([A-Za-z0-9-_]+)/", $requestWord);
                }

                if ($matches == false)
                {
                    return false;
                }
            }

            return true;
        }  
        
        private function findRouteParams(string $requestRoute, string $informedRoute)
        { 
            $params = [];
            
            $explodedRequestRoute = explode("/", $requestRoute);
            $explodedInformedRequestRoute = explode("/", $informedRoute);

            for ($i = 0; $i < count($explodedRequestRoute); $i++)
            {
                $requestWord = $explodedRequestRoute[$i];
                $informedWord = $explodedInformedRequestRoute[$i];

                $maybeItIsRouteParameter = false;
                if ($requestWord !== $informedWord) 
                {
                    $maybeItIsRouteParameter = true;
                }
                else
                {
                    continue;
                }

                $matches = false;
                if ($maybeItIsRouteParameter) 
                {
                    $matches = preg_match("/:([A-Za-z0-9-_]+)/", $requestWord);
                }

                $isValueEmpty = false;
                if ($matches)
                {
                    $key = str_replace(":", "", $requestWord);
                    $isValueEmpty = $informedWord === "";                    
                }

                if ($isValueEmpty === false) 
                {
                    $params[$key] = $informedWord;
                }
            }

            return $params;
        }
    }
?>