<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Provider;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\AuthenticationToken;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\Exception\AuthenticationTokenProviderException;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Provider\Option\JwtTokenProviderOptions;
use Mvreisg\GamebaseBackend\Infrastructure\Serialization\Authentication\Data\AuthenticationDataSerializer;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Clock;

class JwtTokenProvider implements AuthenticationTokenProvider
{
    private JwtTokenProviderOptions $options;
    private Clock $clock;

    public function __construct(JwtTokenProviderOptions $options, Clock $clock)
    {
        $this->options = $options;
        $this->clock = $clock;
    }

    public function encode(AuthenticationData $data, \DateInterval $duration): string
    {
        try {
            $issuedAt = $this->clock->now();
            $expireAt = $issuedAt->add($duration)->getTimestamp();

            $payload = [
                "iat" => $issuedAt->getTimestamp(),
                "exp" => $expireAt,
                "sub" => AuthenticationDataSerializer::toArray($data)
            ];

            $token = JWT::encode(
                $payload,
                $this->options->getKey(),
                "HS256"
            );

            return $token;
        } catch (\Throwable $e) {
            throw new AuthenticationTokenProviderException(
                $e->getMessage()
            );
        }
    }

    public function decode(string $token): AuthenticationToken
    {
        try {
            $payload = JWT::decode(
                $token,
                new Key(
                    $this->options->getKey(),
                    "HS256"
                )
            );
            $data = AuthenticationDataSerializer::toObject($payload->sub);
            return new AuthenticationToken(
                new \DateTimeImmutable("@{$payload->iat}"),
                new \DateTimeImmutable("@{$payload->exp}"),
                $data
            );
        } catch (\Throwable $e) {
            throw new AuthenticationTokenProviderException(
                $e->getMessage()
            );
        }
    }

    public function validate(AuthenticationToken $token): void
    {
        if ($token->getIssuedAt()->getTimestamp() > $this->clock->now()->getTimestamp()) {
            throw new AuthenticationTokenProviderException(
                "Issued in the future."
            );
        }

        if ($this->clock->now()->getTimestamp() > $token->getExpiresAt()->getTimestamp()) {
            throw new AuthenticationTokenProviderException(
                "Expired token."
            );
        }
    }
}
