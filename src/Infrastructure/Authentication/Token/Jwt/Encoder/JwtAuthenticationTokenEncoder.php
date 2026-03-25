<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Encoder;

use Firebase\JWT\JWT;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\Exceptions\AuthenticationTokenEncoderException;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;

class JwtAuthenticationTokenEncoder implements AuthenticationTokenEncoder
{
    private string $key;
    private JwtAuthenticationTokenClock $clock;

    public function __construct(string $key, JwtAuthenticationTokenClock $clock)
    {
        $this->key = $key;
        $this->clock = $clock;
    }

    public function encode(AuthenticationData $data, \DateInterval $duration): EncodedAuthenticationToken
    {
        try {
            $secretKey = $this->key;
            $issuedAt = $this->clock->now();
            $expireAt = $issuedAt->getTimestamp() + $duration->s;

            $payload = [
                "iat" => $issuedAt->getTimestamp(),
                "exp" => $expireAt,
                "sub" => $data->toArray()
            ];

            $token = JWT::encode($payload, $secretKey, "HS256");

            return new EncodedAuthenticationToken($token);
        } catch (\Throwable $e) {
            throw new AuthenticationTokenEncoderException(
                $e->getMessage()
            );
        }
    }
}
