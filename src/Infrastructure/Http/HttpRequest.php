<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;

class HttpRequest
{
    private string $method;

    private string $route;

    private $queries;

    private $params;

    private string $body;

    private array $headers;

    public function __construct(
        string $method,
        string $route,
        $queries = [],
        $params = [],
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

    public function getMethod()
    {
        return $this->method;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getQueries()
    {
        return $this->queries;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function parseBodyFromJSONString()
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
