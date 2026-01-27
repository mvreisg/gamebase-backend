<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Entities;

use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteQueryTypes;

class HttpQuery
{
    private HttpRouteQueryTypes $type;
    private mixed $value;

    public function __construct(
        HttpRouteQueryTypes $type,
        mixed $value,
    ) {
        $this->type = $type;
        $this->value = $value;
    }

    public function getType(): HttpRouteQueryTypes
    {
        return $this->type;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
