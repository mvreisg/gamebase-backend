<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Encoder;

use Firebase\JWT\JWT;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class JwtAuthenticationTokenEncoder implements AuthenticationTokenEncoder
{
    private JwtAuthenticationTokenClock $clock;

    public function __construct(JwtAuthenticationTokenClock $clock)
    {
        $this->clock = $clock;
    }

    public function encode(AuthenticationData $data, \DateInterval $duration): EncodedAuthenticationToken
    {
        $oneDayInSeconds = 60 * 60 * 24;
        $secretKey = DotenvEnvironment::get("JWT_SECRET");
        $issuedAt = $this->clock->now();
        $expireAt = $issuedAt->getTimestamp() + $duration->d * $oneDayInSeconds;

        $payload = [
            "iat" => $issuedAt->getTimestamp(),
            "exp" => $expireAt,
            "sub" => $data->toArray()
        ];

        $token = JWT::encode($payload, $secretKey, "HS256");

        return new EncodedAuthenticationToken($token);
    }
}
