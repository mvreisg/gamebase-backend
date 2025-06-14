<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Http;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;

class HttpResponse
{
    private array $headers;
    private array $body;

    public function __construct(array $headers = [], array $body = [])
    {
        $this->headers = $headers;
        $this->body = $body;
    }

    public function setBody(array $data): HttpResponse
    {
        $this->body = $data;
        return $this;
    }

    public function addHeader(string $header): HttpResponse
    {
        $this->headers[] = $header;
        return $this;
    }

    public function setStatus(string $setStatus): HttpResponse
    {
        header($setStatus);
        return $this;
    }

    public function send(string $contentType = 'default'): void
    {
        foreach ($this->headers as $header) {
            header($header);
        }

        switch ($contentType) {
            case 'default':
            default:
                print_r($this->body);
                break;
            case HttpRouter::$CONTENT_TYPES['JSON']:
                header(HttpRouter::$CONTENT_TYPES['JSON']);
                try {
                    print(
                        json_encode($this->body)
                    );
                } catch (HttpJsonParseException $e) {
                    throw $e;
                }
                break;
        }
    }
}
