<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;

class HttpRoute
{
    private HttpMethodTypesEnum $method;
    private string $separator;
    /**
     * @var HttpRoutePart[] $pathParts
     */
    private array $pathParts;
    private int $pathPartIndex;
    private $callback;

    public function __construct()
    {
        $this->pathPartIndex = 0;
    }

    public function getMethod(): HttpMethodTypesEnum
    {
        return $this->method;
    }

    public function setMethod(HttpMethodTypesEnum $method): HttpRoute
    {
        $this->method = $method;
        return $this;
    }

    public function setSeparator(string $separator = "/"): HttpRoute
    {
        $this->separator = $separator;
        return $this;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getPathPart(int $index): ?HttpRoutePart
    {
        $exists = isset($this->pathParts[$index]);
        if ($exists) {
            return $this->pathParts[$index];
        }
        return null;
    }

    public function getPathPartsCount(): int
    {
        return count($this->pathParts);
    }

    public function appendPathPart(HttpRoutePart $part): HttpRoute
    {
        $this->pathParts[$this->pathPartIndex] = $part;
        $this->pathPartIndex++;
        return $this;
    }

    public function getStringPath(): string
    {
        return join(
            $this->getSeparator(),
            array_map(
                fn (HttpRoutePart $part) => $part->getName(),
                $this->pathParts
            )
        );
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function setCallback(callable $callback): HttpRoute
    {
        $this->callback = $callback;
        return $this;
    }
}
