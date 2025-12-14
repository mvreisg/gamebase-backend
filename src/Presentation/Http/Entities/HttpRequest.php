<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;

class HttpRequest
{
    private HttpMethodTypesEnum $method;
    private HttpRoute $route;
    /**
     * @var HttpQuery[] $queries
     */
    private array $queries;
    private array $params;
    private string $body;
    private array $headers;
    private array $parsedBody;
    private bool $isBodyParsed;

    public function __construct(
        HttpMethodTypesEnum $method,
        HttpRoute $route,
        array $queries = [],
        array $params = [],
        string $body = '',
        array $headers = [],
    ) {
        $this->method = $method;
        $this->route = $route;
        $this->queries = $queries;
        $this->params = $params;
        $this->body = $body;
        $this->headers = $headers;
        $this->parsedBody = [];
        $this->isBodyParsed = false;
    }

    public function getMethod(): HttpMethodTypesEnum
    {
        return $this->method;
    }

    public function getRoute(): HttpRoute
    {
        return $this->route;
    }

    public function getHeaderOrDieTrying(string $key): string
    {
        $exists = isset($this->headers[$key]);
        if ($exists === false) {
            throw new HttpBadRequestException(
                "$key header not informed!"
            );
        }
        return $this->headers[$key];
    }

    public function getParamOrDieTrying(string $key): mixed
    {
        try {
            $exists = isset($this->params[$key]);
            if ($exists === false) {
                throw new HttpBadRequestException(
                    "$key parameter not informed!"
                );
            }
            return $this->params[$key];
        } catch (\Throwable $e) {
            throw $e;
        }
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

    public function getParsedBodyPartOrDieTrying(string $key): mixed
    {
        $this->parseBodyFromJsonString();
        $exists = isset($this->parsedBody[$key]);
        if ($exists === false) {
            throw new HttpBadRequestException(
                "$key field not informed!"
            );
        }
        return $this->parsedBody[$key];
    }

    public function parseBodyFromJsonString(): void
    {
        try {
            if ($this->isBodyParsed) {
                return;
            }
            $isAssociative = true;
            $result = json_decode($this->body, $isAssociative);
            if ($result == false) {
                throw new HttpBadRequestException(
                    "Malformed JSON!"
                );
            }
            $this->parsedBody = $result;
            $this->isBodyParsed = true;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
