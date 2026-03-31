<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Provider;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\AuthenticationToken;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\Exceptions\TokenProviderException;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\TokenProvider;
use Mvreisg\GamebaseBackend\Infrastructure\Serialization\Authentication\Data\AuthenticationDataSerializer;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Clock;

class JwtTokenProvider implements TokenProvider
{
    private string $key;
    private Clock   $clock;

    public function __construct(string $key, Clock $clock)
    {
        $this->key = $key;
        $this->clock = $clock;
    }

    public function encode(AuthenticationData $data, \DateInterval $duration): string
    {
        try {
            $secretKey = $this->key;
            $issuedAt = $this->clock->now();
            $expireAt = $issuedAt->add($duration)->getTimestamp();

            $payload = [
                "iat" => $issuedAt->getTimestamp(),
                "exp" => $expireAt,
                "sub" => AuthenticationDataSerializer::toArray($data)
            ];

            $token = JWT::encode($payload, $secretKey, "HS256");

            return $token;
        } catch (\Throwable $e) {
            throw new TokenProviderException(
                $e->getMessage()
            );
        }
    }

    public function decode(string $token): AuthenticationToken
    {
        try {
            $payload = JWT::decode($token, new Key($this->key, "HS256"));
            $data = AuthenticationDataSerializer::toObject($payload->sub);
            return new AuthenticationToken(
                new \DateTimeImmutable("@{$payload->iat}"),
                new \DateTimeImmutable("@{$payload->exp}"),
                $data
            );
        } catch (\Throwable $e) {
            throw new TokenProviderException(
                $e->getMessage()
            );
        }
    }

    public function validate(AuthenticationToken $token): void
    {
        if ($token->getIssuedAt()->getTimestamp() > $this->clock->now()->getTimestamp()) {
            throw new TokenProviderException(
                "Issued in the future."
            );
        }

        if ($this->clock->now()->getTimestamp() > $token->getExpiresAt()->getTimestamp()) {
            throw new TokenProviderException(
                "Expired token."
            );
        }
    }
}
