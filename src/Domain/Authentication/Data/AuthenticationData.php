<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authentication\Data;

use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Serialization\Casing\SerializationCasingTypes;

class AuthenticationData
{
    private Id $userId;
    private Username $username;

    public function __construct(
        Id $userId,
        Username $username,
    ) {
        $this->userId = $userId;
        $this->username = $username;
    }

    public static function toObject(\stdClass $data, SerializationCasingTypes $serializationCasingType = SerializationCasingTypes::SnakeCase): self
    {
        switch ($serializationCasingType) {
            case SerializationCasingTypes::LowerCamelCase:
                return new self(
                    Id::make($data->userId),
                    Username::make($data->username)
                );
            case SerializationCasingTypes::UpperCamelCase:
                return new self(
                    Id::make($data->UserId),
                    Username::make($data->Username)
                );
            case SerializationCasingTypes::SnakeCase:
                return new self(
                    Id::make($data->user_id),
                    Username::make($data->username)
                );
            default:
                throw new \DomainException(
                    "Untreated authentication data serialization casing type: $serializationCasingType"
                );
        }
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function toArray(SerializationCasingTypes $serializationCasingType = SerializationCasingTypes::SnakeCase): array
    {
        $userIdValue = $this->userId->getValue();
        $usernameValue = $this->username->getValue();
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
                throw new \DomainException(
                    "Untreated authentication data serialization casing type: $serializationCasingType"
                );
        }
    }
}
