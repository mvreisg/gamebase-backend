<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethods;

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
        if (str_contains($type, "application/json")) {
            return json_decode($stream, true);
        }

        return null;
    }

    public function getMethod(): HttpMethods
    {
        return $this->method;
    }

    public function getRoute(): HttpRoute
    {
        return $this->route;
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

    public function getParamOrDieTrying(string $key): mixed
    {
        $exists = isset($this->params[$key]);
        if ($exists === false) {
            return null;
        }
        return $this->params[$key];
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

    public function getBodyOrDieTrying(string $key): mixed
    {
        $exists = isset($this->body[$key]);
        if ($exists === false) {
            return null;
        }
        return $this->body[$key];
    }
}
