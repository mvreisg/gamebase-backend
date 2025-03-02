<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;

/**
 * HTTP request class.
 * Handles with the request data and provides request operations.
 */
class HttpRequest
{
    /**
     * @var string $method The request method (GET, POST, PUT, etc...) as string.
     */
    private string $method;

    /**
     * @var string $route The request route ("/game", etc...) as string.
     */
    private string $route;

    /**
     * @var Map<string,string> $queries The request query parameters (?a=1&b=2...) as a string map.
     */
    private $queries;

    /**
     * @var Map<string,string> $params The request route paarmeters (/game/1/genre/2...) as a string map.
     */
    private $params;

    /**
     * @var string $body The request body as a string value.
     */
    private string $body;

    private array $headers;

    /**
     * HTTP request class constructor.
     * @param string $method The request method.
     * @param string $route The request route.
     * @param Map<string,string> $queries The request query parameters.
     * @param Map<string,string> $params The request route parameters.
     * @param string $body The request body.
     */
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

    /**
     * Gets the request method.
     * @return string The request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Gets the request route.
     * @return string The request route.
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Gets the request query parameters.
     * @return Map<string,string> The request query parameters.
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * Gets the request route parameters.
     * @return Map<string,string> The request route parameters.
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Gets the request body.
     * @return string The request body.
     */
    public function getBody()
    {
        return $this->body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Method that gets the body as a string in json format, and parses it to a PHP data structure.
     * @return mixed The content of the parsed JSON string body as a PHP data structure.
     * @throws HttpJsonParseException Throwed if the an error occurs in the JSON parsing.
     */
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
