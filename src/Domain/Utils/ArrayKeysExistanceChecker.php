<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Utils;

class ArrayKeysExistanceChecker
{
    public static function checkAndReturnMissing(array $container, array $requiredKeys): array
    {
        $missingKeys = [];
        foreach ($requiredKeys as $requiredKey) {
            if (isset($container[$requiredKey]) === false) {
                $missingKeys[] = $requiredKey;
            }
        }
        return $missingKeys;
    }
}
