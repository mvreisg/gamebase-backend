<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Utils\Arrays;

class ArrayMissingKeysInformer
{
    public static function getStatusAsArray(
        array $missingKeys,
        string $message,
        string $keysName
    ): array {
        return [
            "message" => $message,
            $keysName => $missingKeys
        ];
    }
}
