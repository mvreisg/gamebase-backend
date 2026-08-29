<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Serialization\Authentication\Data;

use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Serialization\Casing\SerializationCasingTypes;
use Mvreisg\GamebaseBackend\Infrastructure\Serialization\Exception\SerializationException;

class AuthenticationDataSerializer
{
    public static function toObject(
        \stdClass $data,
        SerializationCasingTypes $serializationCasingType = SerializationCasingTypes::SnakeCase
    ): AuthenticationData {
        switch ($serializationCasingType) {
            case SerializationCasingTypes::LowerCamelCase:
                return new AuthenticationData(
                    Id::create($data->userId),
                    Username::create($data->username)
                );
            case SerializationCasingTypes::UpperCamelCase:
                return new AuthenticationData(
                    Id::create($data->UserId),
                    Username::create($data->Username)
                );
            case SerializationCasingTypes::SnakeCase:
                return new AuthenticationData(
                    Id::create($data->user_id),
                    Username::create($data->username)
                );
            default:
                throw new SerializationException(
                    "Untreated authentication data serialization casing type: $serializationCasingType"
                );
        }
    }

    public static function toArray(
        AuthenticationData $data,
        SerializationCasingTypes $serializationCasingType = SerializationCasingTypes::SnakeCase
    ): array {
        $userIdValue = $data->getUserId()->getValue();
        $usernameValue = $data->getUsername()->getValue();
        switch ($serializationCasingType) {
            case SerializationCasingTypes::LowerCamelCase:
                return [
                    "userId" => $userIdValue,
                    "username" => $usernameValue
                ];
            case SerializationCasingTypes::UpperCamelCase:
                return [
                    "UserId" => $userIdValue,
                    "Username" => $usernameValue
                ];
            case SerializationCasingTypes::SnakeCase:
                return [
                    "user_id" => $userIdValue,
                    "username" => $usernameValue
                ];
            default:
                throw new SerializationException(
                    "Untreated authentication data serialization casing type: $serializationCasingType"
                );
        }
    }
}
