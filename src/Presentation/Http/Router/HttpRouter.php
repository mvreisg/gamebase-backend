<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Router;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceInvalidCredentialsException;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\Exceptions\AuthorizationServiceUnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Exceptions\TokenCacheException;
use Mvreisg\GamebaseBackend\Domain\Data\Exceptions\DataException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpHeader;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpQuery;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRoute;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethods;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteQueryTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodes;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpException;

class HttpRouter
{
    public static function make(): HttpRouter
    {
        return new HttpRouter();
    }

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

    private function sendJson(HttpStatusCodes $statusCode, ?string $message, ?array $headers): void
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
                    $this->sendJson(
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

            $params = [];
            $queries = [];

            /**
             * @var HttpRoute
             */
            $passedRoute = null;
            foreach ($this->routes as $candidateRoute) {
                if ($candidateRoute->getMethod() !== $method) {
                    continue;
                }
                $numberOfRouteParts = count(
                    array_filter(
                        $candidateRoute->getRoutePartTypes(),
                        function (HttpRouteParameterTypes $routePartType) {
                            return $routePartType === HttpRouteParameterTypes::Route;
                        }
                    )
                );
                $parameterCount = 0;
                $isTheCorrectRoute = true;
                $routePartsCount = $candidateRoute->getPathPartsCount();
                if ($routePartsCount !== $tokenizedRouteCount) {
                    continue;
                }
                for ($i = 0; $i < $routePartsCount; $i++) {
                    $routePartType = $candidateRoute->getPathPart($i)->getType();
                    $routePartName = $candidateRoute->getPathPart($i)->getName();
                    if ($routePartType === HttpRouteParameterTypes::Route) {
                        if ($candidateRoute->getPathPart($i)->getName() !== $tokenizedRoute[$i]) {
                            $isTheCorrectRoute = false;
                            break;
                        }
                    } else {
                        $parameter = trim(urldecode($tokenizedRoute[$i]));
                        $isTheCorrectParameter = true;
                        switch ($routePartType) {
                            case HttpRouteParameterTypes::Route:
                                throw new HttpException(
                                    "Unexpected value: $routePartType"
                                );
                            case HttpRouteParameterTypes::Text:
                                $isMatchingValue =
                                    is_string($parameter) &&
                                    $parameter !== "true" &&
                                    $parameter !== "false" &&
                                    $parameter !== "0" &&
                                    filter_var($parameter, FILTER_VALIDATE_INT) === false &&
                                    filter_var($parameter, FILTER_VALIDATE_FLOAT) === false &&
                                    filter_var($parameter, FILTER_VALIDATE_BOOL) === false;
                                if ($isMatchingValue === false) {
                                    $isTheCorrectParameter = false;
                                }
                                break;
                            case HttpRouteParameterTypes::Integer:
                                $isMatchingValue =
                                    filter_var($parameter, FILTER_VALIDATE_INT) ||
                                    $parameter === "0";
                                if ($isMatchingValue) {
                                    $parameter = intval($parameter);
                                } else {
                                    $isTheCorrectParameter = false;
                                }
                                break;
                            case HttpRouteParameterTypes::Decimal:
                                $isMatchingValue = filter_var($parameter, FILTER_VALIDATE_FLOAT);
                                if ($isMatchingValue) {
                                    $parameter = floatval($parameter);
                                } else {
                                    $isTheCorrectParameter = false;
                                }
                                break;
                            case HttpRouteParameterTypes::Boolean:
                                $isMatchingValue = filter_var($parameter, FILTER_VALIDATE_BOOL);
                                if ($isMatchingValue) {
                                    $parameter = boolval($parameter);
                                } else {
                                    $isTheCorrectParameter = false;
                                }
                                break;
                            default:
                                $this->sendJson(
                                    HttpStatusCodes::InternalServerError,
                                    "Untreated route type: $routePartType",
                                    null
                                );
                                return;
                        }
                        if ($isTheCorrectParameter === false) {
                            $params = [];
                            break;
                        }
                        $params[$routePartName] = $parameter;
                        $parameterCount++;
                    }
                }

                if ($tokenizedRouteCount - $parameterCount !== $numberOfRouteParts) {
                    $isTheCorrectRoute = false;
                    $params = [];
                    continue;
                }

                if ($isTheCorrectRoute === false) {
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

                $passedRoute = $candidateRoute;
                break;
            }

            if ($passedRoute === null) {
                $this->sendJson(
                    HttpStatusCodes::NotFound,
                    "Route not found!",
                    null
                );
                return;
            }

            $request = new HttpRequest(
                $method,
                $passedRoute,
                $queries,
                $params,
                $body,
                $this->headers
            );
            $callback = $passedRoute->getCallback();
            $response = $callback($request);
            $this->send(
                $response->getStatusCode(),
                $response->hasReadableBody() ? $response->parseBody() : null,
                $response->getHeaders()
            );
        } catch (
            HttpException |
            DataException
            $e
        ) {
            $this->sendJson(
                HttpStatusCodes::BadRequest,
                $e->getMessage(),
                null
            );
        } catch (
            RepositoryUnexistantRegisterException |
            RepositoryDuplicatedRegisterException |
            AuthenticationServiceInvalidCredentialsException |
            AuthorizationServiceUnauthorizedException |
            TokenCacheException
            $e
        ) {
            $this->sendJson(
                HttpStatusCodes::Unauthorized,
                $e->getMessage(),
                null
            );
        } catch (\Throwable $e) {
            $this->sendJson(
                HttpStatusCodes::InternalServerError,
                $e->getMessage(),
                null
            );
            throw $e;
        }
    }
}
