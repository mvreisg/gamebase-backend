<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethods;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRequestBodyPartTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpException;

class HttpRequest
{
    private HttpMethods $method;
    private HttpRoute $route;
    /**
     * @var HttpQuery[] $queries
     */
    private array $queries;
    private array $params;
    /**
     * @var HttpHeader[] $headers
     */
    private array $headers;
    private array $body;

    public function __construct(
        HttpMethods $method,
        HttpRoute $route,
        array $queries = [],
        array $params = [],
        string $stream = "",
        array $headers = [],
    ) {
        $this->method = $method;
        $this->route = $route;
        $this->queries = $queries;
        $this->params = $params;
        $this->headers = $headers;
        $this->body = $this->parseBody($stream) ?? [];
    }

    public function parseBody(string $stream): mixed
    {
        if ($stream === "") {
            return null;
        }
        $type = "";
        foreach ($this->headers as $header) {
            if ($header->getKey() === "Content-Type") {
                $type = $header->getValue();
            }
        }
        $body = [];
        if (str_contains($type, "application/json")) {
            $body = json_decode($stream, true);
            if ($body === null || $body === false) {
                throw new HttpException(
                    "Invalid JSON body"
                );
            }
        }
        return $body;
    }

    public function hasReadableBody(): bool
    {
        return $this->body !== null;
    }

    public function getMethod(): HttpMethods
    {
        return $this->method;
    }

    public function getRoute(): HttpRoute
    {
        return $this->route;
    }

    public function getParamsCount(): int
    {
        return count($this->params);
    }

    public function getParamValueAt(int $index): mixed
    {
        return $this->params[$index];
    }

    public function getParamNameAt(int $index): string
    {
        return $this->getRoute()->getPathPart($index)->getName();
    }

    public function getHeaderOrDieTrying(string $key): ?HttpHeader
    {
        foreach ($this->headers as $header) {
            if ($header->getKey() === $key) {
                return $header;
            }
        }
        return null;
    }

    public function getParamOrDieTrying(string $key, HttpRouteParameterTypes $type): mixed
    {
        $exists = isset($this->params[$key]);
        if ($exists === false) {
            return null;
        }
        $value = $this->params[$key];
        switch ($type) {
            case HttpRouteParameterTypes::Text:
                $isMatchingValue =
                    is_string($value) &&
                    $value !== "true" &&
                    $value !== "false" &&
                    $value !== "0" &&
                    filter_var($value, FILTER_VALIDATE_INT) === false &&
                    filter_var($value, FILTER_VALIDATE_FLOAT) === false &&
                    filter_var($value, FILTER_VALIDATE_BOOL) === false;
                if ($isMatchingValue === false) {
                    throw new HttpException(
                        "Parameter key '$key' is not a string"
                    );
                }
                break;
            case HttpRouteParameterTypes::Integer:
                $isMatchingValue =
                    filter_var($value, FILTER_VALIDATE_INT) ||
                    $value === "0";
                if ($isMatchingValue) {
                    $value = intval($value);
                } else {
                    throw new HttpException(
                        "Parameter key '$key' is not an integer"
                    );
                }
                break;
            case HttpRouteParameterTypes::Decimal:
                $isMatchingValue = filter_var($value, FILTER_VALIDATE_FLOAT);
                if ($isMatchingValue) {
                    $value = floatval($value);
                } else {
                    throw new HttpException(
                        "Parameter key '$key' is not a float"
                    );
                }
                break;
            case HttpRouteParameterTypes::Boolean:
                $isMatchingValue =
                    filter_var($value, FILTER_VALIDATE_BOOL)
                    && $value !== "0"
                    && $value !== "1"
                    && $value !== "true"
                    && $value !== "false"
                    && $value !== 0
                    && $value !== 1;
                if ($isMatchingValue) {
                    $value = boolval($value);
                } else {
                    throw new HttpException(
                        "Parameter key '$key' is not a boolean"
                    );
                }
                break;
            default:
                throw new HttpException(
                    "Invalid route part type"
                );
        }
        return $value;
    }

    public function getQueryOrDieTrying(string $key): ?HttpQuery
    {
        try {
            $exists = isset($this->queries[$key]);
            if ($exists === false) {
                return null;
            }
            return $this->queries[$key];
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function getBodyOrDieTrying(string $key, HttpRequestBodyPartTypes $type): mixed
    {
        $exists = isset($this->body[$key]);
        if ($exists === false) {
            throw new HttpException(
                "Body key '$key' not found"
            );
        }
        $value = $this->body[$key];
        switch ($type) {
            case HttpRequestBodyPartTypes::String:
                if (
                    is_string($value) === false ||
                    filter_var($value, FILTER_VALIDATE_INT) === true ||
                    filter_var($value, FILTER_VALIDATE_FLOAT) === true ||
                    filter_var($value, FILTER_VALIDATE_BOOL) === true
                ) {
                    throw new HttpException(
                        "Body key '$key' is not a string"
                    );
                }
                break;
            case HttpRequestBodyPartTypes::Int:
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    throw new HttpException(
                        "Body key '$key' is not an integer"
                    );
                }
                break;
            case HttpRequestBodyPartTypes::Float:
                if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
                    throw new HttpException(
                        "Body key '$key' is not a float"
                    );
                }
                break;
            case HttpRequestBodyPartTypes::Bool:
                if (
                    $value === "0" ||
                    $value === "1" ||
                    $value === "true" ||
                    $value === "false" ||
                    $value === 0 ||
                    $value === 1
                ) {
                    throw new HttpException(
                        "Body key '$key' is not a boolean"
                    );
                }
                break;
            default:
                throw new HttpException(
                    "Invalid body part type"
                );
                break;
        }
        return $value;
    }
}
