<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpMethodTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;

class HttpRoute
{
    private HttpMethodTypesEnum $method;
    private string $separator;
    private array $parts;
    private int $head;
    private $callback;

    public function __construct()
    {
        $this->head = 0;
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

    public function setSeparator(string $separator = '/'): HttpRoute
    {
        $this->separator = $separator;
        return $this;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getPart(int $index): HttpRoutePart|null
    {
        if (isset($this->parts[$index])) {
            return $this->parts[$index];
        }
        return null;
    }

    public function getPartsCount(): int
    {
        return count($this->parts);
    }

    public function append(string $name, HttpRouteParameterTypesEnum $type): HttpRoute
    {
        return $this->appendObject(new HttpRoutePart($name, $type));
    }

    public function appendObject(HttpRoutePart $part): HttpRoute
    {
        $this->parts[$this->head] = $part;
        $this->head++;
        return $this;
    }

    public function setParts(array $parts): HttpRoute
    {
        $this->parts = $parts;
        return $this;
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
