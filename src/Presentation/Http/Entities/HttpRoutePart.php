<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;

class HttpRoutePart
{
    public static function make(string $name, HttpRouteParameterTypes $type): HttpRoutePart
    {
        return new HttpRoutePart($name, $type);
    }

    private string $name;

    private HttpRouteParameterTypes $type;

    public function __construct(string $name, HttpRouteParameterTypes $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): HttpRouteParameterTypes
    {
        return $this->type;
    }
}
