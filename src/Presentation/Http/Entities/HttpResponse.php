<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpContentTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodes;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpException;

class HttpResponse
{
    /**
     * @var HttpHeader[]
     */
    private array $headers;
    private array $body;
    private ?HttpStatusCodes $statusCode;

    public function __construct(
        array $headers = [],
        array $body = []
    ) {
        $this->headers = $headers;
        $this->body = $body;
        $this->statusCode = null;
    }

    public static function make(): self
    {
        return new self();
    }

    public function getStatusCode(): ?HttpStatusCodes
    {
        return $this->statusCode;
    }

    /**
     * @return HttpHeader[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasReadableBody(): bool
    {
        return $this->body !== [];
    }

    public function setBody(array $data): HttpResponse
    {
        $this->body = $data;
        return $this;
    }

    public function addHeader(HttpHeader $header): HttpResponse
    {
        $this->headers[] = $header;
        return $this;
    }

    private function setStatus(HttpStatusCodes $statusCode): HttpResponse
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setStatusOk(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodes::Ok);
    }

    public function setStatusCreated(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodes::Created);
    }

    public function setStatusNoContent(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodes::NoContent);
    }

    public function setStatusBadRequest(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodes::BadRequest);
    }

    public function setStatusUnauthorized(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodes::Unauthorized);
    }

    public function setStatusForbidden(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodes::Forbidden);
    }

    public function setStatusNotFound(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodes::NotFound);
    }

    public function setStatusInternalServerError(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodes::InternalServerError);
    }

    private function setContentType(HttpContentTypes $contentType): void
    {
        switch ($contentType) {
            default:
                throw new HttpException(
                    "Untreated Content-Type: {$contentType->value}"
                );
            case HttpContentTypes::Text:
                $this->headers[] = new HttpHeader(HttpContentTypes::Text->value);
                break;
            case HttpContentTypes::Json:
                $this->headers[] = new HttpHeader(HttpContentTypes::Json->value);
                break;
        }
    }

    public function setContentTypeAsText(): void
    {
        $this->setContentType(
            HttpContentTypes::Text
        );
    }

    public function setContentTypeAsJson(): void
    {
        $this->setContentType(
            HttpContentTypes::Json
        );
    }

    public function parseBody(): string
    {
        $type = "";
        foreach ($this->headers as $header) {
            if ($header->getKey() === "Content-Type") {
                $type = $header->getValue();
            }
        }
        if (str_contains($type, "application/json")) {
            return json_encode($this->body);
        }

        throw new HttpException(
            "Unknown header type: {$type}"
        );
    }
}
