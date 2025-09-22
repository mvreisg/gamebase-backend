<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpJsonParseException;

class HttpRequest
{
    private string $method;
    private HttpRoute $route;
    private array $queries;
    private array $params;
    private string $body;
    private array $headers;

    public function __construct(
        string $method,
        HttpRoute $route,
        array $queries = [],
        array $params = [],
        string $body = '',
        array $headers = []
    ) {
        $this->method = $method;
        $this->route = $route;
        $this->queries = $queries;
        $this->params = $params;
        $this->body = $body;
        $this->headers = $headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRoute(): HttpRoute
    {
        return $this->route;
    }

    public function getQueries(): array
    {
        return $this->queries;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function parseBodyFromJSONString(): mixed
    {
        try {
            $isAssociative = true;
            $result = json_decode($this->body, $isAssociative);
            if ($result == false) {
                throw new HttpJsonParseException();
            }
            return $result;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
