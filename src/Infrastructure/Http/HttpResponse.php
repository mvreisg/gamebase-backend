<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;

/**
 * HTTP response class.
 * Handles with the response data and provides response operations.
 */
class HttpResponse
{
    /**
     * @var List<string> $headers A list of the response headers.
     */
    private $headers;

    /**
     * @var string $body The request body.
     */
    private string $body;

    /**
     * HTTP response class constructor.
     * @param List<string> $headers A list of the response headers.
     * @param string $body The response body.
     */
    public function __construct($headers = [], string $body = '')
    {
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Appends a string into the response body.
     * @param string $data The string to be appended to the body.
     * @return HttpResponse The same instance that called this function.
     */
    public function appendString(string $data)
    {
        $this->body .= $data;
        return $this;
    }

    /**
     * Appends a array into the response body.
     * @param array $data The data object to be appended to the body.
     * @return HttpResponse The same instance that called this function.
     */
    public function appendArray(array $data)
    {
        $result = json_encode($data);
        if ($result === false) {
            throw new HttpJsonParseException('Erro ao codificar uma estrutura de dados PHP para uma string JSON.');
        }
        $this->body .= $result;
        return $this;
    }

    /**
     * Adds a header into the response object.
     * @param string $header The name of the header that is to be added.
     * @return HttpResponse The same instance that called this function.
     */
    public function addHeader(string $header)
    {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * Sets a status (code) header fro the response.
     * @param string $status The name of the header status that is to be setted.
     * @return HttpResponse The same instance that called this function.
     */
    public function status(string $status)
    {
        header($status);
        return $this;
    }

    /**
     * Sends a response to the requested side via plain text with the headers.
     * @return void
     */
    public function send()
    {
        foreach ($this->headers as $header) {
            header($header);
        }
        print($this->body);
    }

    /**
     * Sends a response to the requested side via JSON with the headers.
     * @return void
     */
    public function sendJSON()
    {
        header(HttpApplication::HEADERS['CONTENT_TYPE_APPLICATION_JSON']);
        foreach ($this->headers as $header) {
            header($header);
        }
        print($this->body);
    }
}
