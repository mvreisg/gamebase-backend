<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Decoder;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Clock\MockAuthenticationTokenClock;

class MockAuthenticationTokenDecoder implements AuthenticationTokenDecoder
{
    private MockAuthenticationTokenClock $clock;

    public function __construct(MockAuthenticationTokenClock $clock)
    {
        $this->clock = $clock;
    }

    public function decode(EncodedAuthenticationToken $token): DecodedAuthenticationToken
    {
        $decoded = base64_decode($token->getToken());
        $split = explode("|", $decoded);
        $count = count($split);
        if ($count < 3) {
            throw new \InvalidArgumentException(
                "Invalid token: invalid number of parts."
            );
        }
        $seconds = intval($split[0]);
        $payload = json_decode($split[1]);
        $issuedAt = intval($split[2]);
        $expiresAt = $issuedAt + $seconds;
        if ($this->clock->now()->getTimestamp() >= $expiresAt) {
            throw new \InvalidArgumentException(
                "Invalid token: expired date."
            );
        }
        $data = AuthenticationData::toObject($payload);
        return new DecodedAuthenticationToken(
            $this->clock->getTimeBasedOnTimestamp($issuedAt),
            $this->clock->getTimeBasedOnTimestamp($expiresAt),
            $data
        );
    }
}
