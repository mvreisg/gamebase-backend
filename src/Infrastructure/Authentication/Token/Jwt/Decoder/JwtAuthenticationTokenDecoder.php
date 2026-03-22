<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Decoder;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\Exceptions\AuthenticationTokenDecoderException;
use Mvreisg\GamebaseBackend\Domain\Session\Data\SessionData;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;

class JwtAuthenticationTokenDecoder implements AuthenticationTokenDecoder
{
    private string $key;
    private JwtAuthenticationTokenClock $clock;

    public function __construct(string $key, JwtAuthenticationTokenClock $clock)
    {
        $this->clock = $clock;
        $this->key = $key;
    }

    public function decode(EncodedAuthenticationToken $token): DecodedAuthenticationToken
    {
        try {
            $payload = JWT::decode($token->getToken(), new Key($this->key, "HS256"));
            $data = SessionData::toObject($payload->sub);
            return new DecodedAuthenticationToken(
                new \DateTimeImmutable("@{$payload->iat}")->setTimezone($this->clock->getTimezone()),
                new \DateTimeImmutable("@{$payload->exp}")->setTimezone($this->clock->getTimezone()),
                $data
            );
        } catch (\Throwable $e) {
            throw new AuthenticationTokenDecoderException(
                $e->getMessage()
            );
        }
    }
}
