<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;

class HttpResponse
{
    private $headers;

    private string $body;

    public function __construct($headers = [], string $body = '')
    {
        $this->headers = $headers;
        $this->body = $body;
    }

    public function appendString(string $data)
    {
        $this->body .= $data;
        return $this;
    }

    public function appendArray(array $data)
    {
        $result = json_encode($data);
        if ($result === false) {
            throw new HttpJsonParseException('Erro ao codificar uma estrutura de dados PHP para uma string JSON.');
        }
        $this->body .= $result;
        return $this;
    }

    public function addHeader(string $header)
    {
        $this->headers[] = $header;
        return $this;
    }

    public function status(string $status)
    {
        header($status);
        return $this;
    }

    public function send()
    {
        foreach ($this->headers as $header) {
            header($header);
        }
        print($this->body);
    }
}
