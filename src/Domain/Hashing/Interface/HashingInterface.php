<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Hashing\Interface;

interface HashingInterface
{
    public function hash(string $text): string;

    public function compare(string $text, string $hash);
}
