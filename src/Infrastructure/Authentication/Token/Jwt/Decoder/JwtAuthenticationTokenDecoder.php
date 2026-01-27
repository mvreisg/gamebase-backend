<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Decoder;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Clock\JwtAuthenticationTokenClock;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

class JwtAuthenticationTokenDecoder implements AuthenticationTokenDecoder
{
    private JwtAuthenticationTokenClock $clock;

    public function __construct(JwtAuthenticationTokenClock $clock)
    {
        $this->clock = $clock;
    }

    public function decode(EncodedAuthenticationToken $token): DecodedAuthenticationToken
    {
        $secretKey = DotenvEnvironment::get("JWT_SECRET");
        $payload = JWT::decode($token->getToken(), new Key($secretKey, "HS256"));
        $data = AuthenticationData::toObject($payload->sub);
        return new DecodedAuthenticationToken(
            $this->clock->getTimeBasedOnTimestamp($payload->iat),
            $this->clock->getTimeBasedOnTimestamp($payload->exp),
            $data
        );
    }
}
