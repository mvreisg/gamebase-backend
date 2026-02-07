<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Enums;

enum HttpMethods: string
{
    case Get = "GET";
    case Post = "POST";
    case Put = "PUT";
    case Patch = "PATCH";
    case Delete = "DELETE";
    case Options = "OPTIONS";
}
