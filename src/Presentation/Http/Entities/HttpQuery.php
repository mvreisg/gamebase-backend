<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteQueryTypesEnum;

class HttpQuery
{
    private HttpRouteQueryTypesEnum $type;
    private mixed $value;

    public function __construct(
        HttpRouteQueryTypesEnum $type,
        mixed $value,
    ) {
        $this->type = $type;
        $this->value = $value;
    }

    public function getType(): HttpRouteQueryTypesEnum
    {
        return $this->type;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
