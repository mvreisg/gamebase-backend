<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;

class HttpRoutePart
{
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
