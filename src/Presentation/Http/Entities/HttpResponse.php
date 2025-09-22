<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUntreatedContentType;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpContentTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodeTypesEnum;

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

    public function setStatus(HttpStatusCodeTypesEnum $statusCodeType): HttpResponse
    {
        header($statusCodeType->value);
        return $this;
    }

    public function setStatusOk(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodeTypesEnum::Ok);
    }

    public function setStatusCreated(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodeTypesEnum::Created);
    }

    public function setStatusNoContent(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodeTypesEnum::NoContent);
    }

    public function setStatusBadRequest(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodeTypesEnum::BadRequest);
    }

    public function setStatusUnauthorized(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodeTypesEnum::Unauthorized);
    }

    public function setStatusForbidden(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodeTypesEnum::Forbidden);
    }

    public function setStatusNotFound(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodeTypesEnum::NotFound);
    }

    public function setStatusInternalServerError(): HttpResponse
    {
        return $this->setStatus(HttpStatusCodeTypesEnum::InternalServerError);
    }

    public function send(HttpContentTypesEnum $contentType): void
    {
        try {
            foreach ($this->headers as $header) {
                header($header);
            }

            switch ($contentType) {
                default:
                    throw new HttpUntreatedContentType(
                        'Untreated content-type: ' . $contentType->value
                    );
                case HttpContentTypesEnum::Text:
                    header(HttpContentTypesEnum::Text->value);

                    print(
                        $this->body
                    );

                    break;
                case HttpContentTypesEnum::Json:
                    header(HttpContentTypesEnum::Json->value);

                    print(
                        json_encode(
                            $this->body
                        )
                    );

                    break;
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function sendText(): void
    {
        $this->send(
            HttpContentTypesEnum::Text
        );
    }

    public function sendJson(): void
    {
        $this->send(
            HttpContentTypesEnum::Json
        );
    }
}
