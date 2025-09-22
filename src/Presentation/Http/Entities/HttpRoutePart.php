<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypesEnum;

class HttpRoutePart
{
    private string $name;

    private HttpRouteParameterTypesEnum $type;

    public function __construct(string $name, HttpRouteParameterTypesEnum $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): HttpRouteParameterTypesEnum
    {
        return $this->type;
    }
}
