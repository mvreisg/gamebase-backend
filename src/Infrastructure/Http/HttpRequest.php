<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;

class HttpRequest
{
    private string $method;
    private string $route;
    private array $queries;
    private array $params;
    private string $body;
    private array $headers;

    public function __construct(
        string $method,
        string $route,
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

    public function getRoute(): string
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
        $isAssociative = true;
        $result = json_decode($this->body, $isAssociative);
        if ($result == false) {
            throw new HttpJsonParseException(
                'Erro ao fazer a decodificação de uma string JSON para uma estrutura de dados PHP.'
            );
        }
        return $result;
    }
}
