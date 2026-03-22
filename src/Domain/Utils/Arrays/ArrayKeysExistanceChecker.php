<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Utils\Arrays;

class ArrayKeysExistanceChecker
{
    public static function checkAndReturnMissingKeys(array $container, array $requiredKeys): array
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
