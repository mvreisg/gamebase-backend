<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Model\Components\Encryption\Defuse;

use Defuse\Crypto\Key;

class HttpDefuseEncryptionComponentModel
{
    public function getKey(): string
    {
        return Key::createNewRandomKey()->saveToAsciiSafeString();
    }
}
