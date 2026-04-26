<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Option;

class HttpOptions
{
    private string $host;
    private string $title;

    public function __construct(string $host, string $title)
    {
        $this->host = $host;
        $this->title = $title;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
