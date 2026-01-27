<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Decoder;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\Encode\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authenticator\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;

class MockAuthenticationTokenDecoder implements AuthenticationTokenDecoder
{
    private JwtAuthenticationTokenClock $clock;

    public function __construct(JwtAuthenticationTokenClock $clock)
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
        $data = AuthenticationData::fromArray($payload);
        return new DecodedAuthenticationToken(
            $this->clock->getTimeBasedOnTimestamp($issuedAt),
            $this->clock->getTimeBasedOnTimestamp($expiresAt),
            $data
        );
    }
}
