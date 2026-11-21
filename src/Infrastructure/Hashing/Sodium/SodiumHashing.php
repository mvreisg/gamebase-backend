<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Hashing\Sodium;

use Mvreisg\GamebaseBackend\Domain\Hashing\Interface\HashingInterface;

class SodiumHashing implements HashingInterface
{
    #[\Override]
    public function hash(string $text): string
    {
        $hash = sodium_crypto_pwhash_str(
            $text,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
        );

        return $hash;
    }

    #[\Override]
    public function compare(string $text, string $hash)
    {
        $isEquals = sodium_crypto_pwhash_str_verify($hash, $text);
        return $isEquals;
    }
}
