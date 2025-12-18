<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Enums;

enum HttpContentTypesEnum: string
{
    case Text = "Content-Type: application/text; charset=utf-8";
    case Json = "Content-Type: application/json; charset=utf-8";
}
