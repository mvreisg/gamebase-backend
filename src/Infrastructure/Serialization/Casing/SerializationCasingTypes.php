<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Serialization\Casing;

enum SerializationCasingTypes
{
    case LowerCamelCase;
    case UpperCamelCase;
    case SnakeCase;
}
