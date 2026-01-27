<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethods;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;

class HttpRoute
{
    private HttpMethods $method;
    private string $separator;
    /**
     * @var HttpRoutePart[] $pathParts
     */
    private array $pathParts;
    private int $pathPartIndex;
    /**
     * @var callable(HttpRequest): HttpResponse $callback
     */
    private $callback;

    public function __construct(string $separator = "/")
    {
        $this->pathPartIndex = 0;
        $this->separator = $separator;
    }

    public function getFullRouteName(): string
    {
        return implode(
            $this->getSeparator(),
            array_map(
                function ($item) {
                    if ($item->getType() !== HttpRouteParameterTypes::Route) {
                        return "({$item->getName()})";
                    }
                    return $item->getName();
                },
                $this->pathParts
            )
        );
    }

    public function getMethod(): HttpMethods
    {
        return $this->method;
    }

    public function setMethod(HttpMethods $method): HttpRoute
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

    /**
     * @return callable(HttpRequest): HttpResponse $callback
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @param callable(HttpRequest): HttpResponse $callback
     */
    public function setCallback(callable $callback): HttpRoute
    {
        $this->callback = $callback;
        return $this;
    }
}
