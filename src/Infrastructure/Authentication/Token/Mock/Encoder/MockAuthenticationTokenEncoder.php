<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Encoder;

use Mvreisg\GamebaseBackend\Domain\Authentication\Data\Encode\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authenticator\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;

class MockAuthenticationTokenEncoder implements AuthenticationTokenEncoder
{
    private JwtAuthenticationTokenClock $clock;

    public function __construct(JwtAuthenticationTokenClock $clock)
    {
        $this->clock = $clock;
    }

    public function encode(AuthenticationData $data, \DateInterval $duration): EncodedAuthenticationToken
    {
        $seconds = $duration->s;
        $emittedAt = strval($this->clock->now()->getTimestamp());
        $token = base64_encode(
            join(
                "|",
                [
                    $seconds,
                    json_encode($data->toArray()),
                    $emittedAt
                ]
            )
        );
        return new EncodedAuthenticationToken($token);
    }
}
