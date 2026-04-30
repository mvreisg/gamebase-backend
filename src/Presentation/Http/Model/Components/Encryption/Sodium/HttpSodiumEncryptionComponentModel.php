<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Model\Components\Encryption\Sodium;

class HttpSodiumEncryptionComponentModel
{
    public function getKey(): string
    {
        $key = sodium_bin2hex(
            sodium_crypto_secretbox_keygen()
        );
        return $key;
    }
}
