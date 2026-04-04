<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validate\AuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Exception\EncryptionException;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\Clock;
use Mvreisg\GamebaseBackend\Domain\Entities\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Interfaces\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exception\RepositoryDuplicatedRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private function createClock(string $timezone): ClockInterface
    {
        $clock = new Clock(
            new \DateTimeZone(
                $timezone
            )
        );
        return $clock;
    }

    private function createEncodedToken(
        string $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken {
        $encodedToken = $this->createMock(EncodedAuthenticationToken::class);
        $encodedToken
            ->method("getToken")
            ->willReturn($token);
        return $encodedToken;
    }

    private function createDecodedToken(
        User $user,
        \DateTimeImmutable $issuedAt,
        \DateTimeImmutable $expiredAt,
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken {
        $decodedToken = $this->createMock(DecodedAuthenticationToken::class);

        $decodedToken
            ->method("getUserId")
            ->willReturn($user->getId());

        $decodedToken
            ->method("getUsername")
            ->willReturn($user->getUsername());

        $decodedToken
            ->method("getIssuedAt")
            ->willReturn($issuedAt);

        $decodedToken
            ->method("getExpiresAt")
            ->willReturn($expiredAt);

        return $decodedToken;
    }

    private function createEmptyTokenCache(
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface {
        $tokenCache = $this->createMock(TokenCacheInterface::class);
        return $tokenCache;
    }

    private function createTokenCache(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken $token
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface {
        $tokenCache = $this->createMock(TokenCacheInterface::class);
        $tokenCache
            ->method("exists")
            ->willReturn(true);
        $tokenCache
            ->method("get")
            ->willReturn(
                $token
            );
        return $tokenCache;
    }

    private function createAuthenticationTokenDecoder(): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder
    {
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        return $authenticationTokenDecoder;
    }

    private function createAuthenticationTokenDecoderWithReturn(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Decoded\DecodedAuthenticationToken $decodedToken
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder {
        $authenticationTokenDecoder = $this->createMock(AuthenticationTokenDecoder::class);
        $authenticationTokenDecoder
            ->method("decode")
            ->willReturn(
                $decodedToken
            );
        return $authenticationTokenDecoder;
    }

    private function createAuthenticationTokenEncoder(): MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Encoder\AuthenticationTokenEncoder
    {
        $authenticationTokenEncoder = $this->createMock(AuthenticationTokenEncoder::class);
        return $authenticationTokenEncoder;
    }


    private function createAuthenticationTokenValidator(
        ClockInterface $clock
    ): AuthenticationTokenValidator {
        $authenticationTokenValidator = new AuthenticationTokenValidator(
            $clock
        );
        return $authenticationTokenValidator;
    }

    private function createAuthenticationService(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface $tokenCache,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder $authenticationTokenDecoder,
        AuthenticationTokenValidator $authenticationTokenValidator
    ): AuthenticationService {
        $authenticationTokenEncoder = $this->createAuthenticationTokenEncoder();
        return new AuthenticationService(
            $tokenCache,
            $authenticationTokenDecoder,
            $authenticationTokenEncoder,
            $authenticationTokenValidator
        );
    }

    private function createUser(
        Id $id,
        Username $username,
        DecodedPassword $password,
        bool $isActive
    ): User {
        $user = new User(
            $username,
            $password,
            $isActive
        );
        $user->setId($id);
        return $user;
    }

    private function createUserRepository(
        User $user
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        return $userRepository;
    }

    private function createUserRepositoryThatThrowsDuplicatedUsernameException(
        User $user
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("checkDuplicatedUsernames")
            ->willThrowException(
                new RepositoryDuplicatedRegisterException(
                    "{$user->getUsername()->getValue()}"
                )
            );
        return $userRepository;
    }

    private function createPermission(): Permission
    {
        $permission = new Permission(
            Name::create("Create"),
            PermissionTypes::getValue(PermissionTypes::Create),
            true
        );
        $permission->setId(Id::create(1));
        return $permission;
    }

    private function createPermissionRepository(
        Permission $permission
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface {
        $permissionRepository = $this->createMock(PermissionRepositoryInterface::class);
        $permissionRepository
            ->method("findById")
            ->willReturn(
                $permission
            );
        return $permissionRepository;
    }

    private function createSector(): Sector
    {
        $sector = new Sector(
            Name::create("User"),
            SectorTypes::getValue(SectorTypes::User),
            true
        );
        $sector->setId(Id::create(1));
        return $sector;
    }

    private function createSectorRepository(
        Sector $sector
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface {
        $sectorRepository = $this->createMock(SectorRepositoryInterface::class);
        $sectorRepository
            ->method("findById")
            ->willReturn(
                $sector
            );
        return $sectorRepository;
    }

    private function createUserSectorPermission(): UserSectorPermission
    {
        $userSectorPermission = new UserSectorPermission(
            Id::create(1),
            Id::create(1),
            Id::create(1),
        );
        return $userSectorPermission;
    }

    private function createUserSectorPermissionRepository(
        UserSectorPermission $userSectorPermission
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface {
        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn(
                new UserSectorPermissionCollection([
                    $userSectorPermission
                ])
            );
        return $userSectorPermissionRepository;
    }

    private function createAuthorizationService(
        Permission $permission,
        Sector $sector,
        UserSectorPermission $userSectorPermission,
        UserRepositoryInterface $userRepository
    ): AuthorizationService {
        $permissionRepository = $this->createPermissionRepository($permission);
        $sectorRepository = $this->createSectorRepository($sector);
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository($userSectorPermission);
        return new AuthorizationService(
            $userRepository,
            $permissionRepository,
            $sectorRepository,
            $userSectorPermissionRepository
        );
    }

    private function createEncrypter(
        string $encryptedPassword
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface {
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("encrypt")
            ->willReturn($encryptedPassword);
        return $encrypter;
    }

    private function createEncrypterThatThrowsExceptionOnEncrypt(
        string $decryptedPassword
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface {
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("encrypt")
            ->willThrowException(
                new EncryptionException(
                    "Encryption failed for password: {$decryptedPassword}"
                )
            );
        return $encrypter;
    }

    private function createUserService(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface $userRepository,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Cache\Token\Interface\TokenCacheInterface $tokenCache,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder $authenticationTokenDecoder,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface $encrypter,
        AuthenticationTokenValidator $authenticationTokenValidator
    ): UserService {
        $permission = $this->createPermission();
        $sector = $this->createSector();
        $userSectorPermission = $this->createUserSectorPermission();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $authenticationTokenDecoder,
            $authenticationTokenValidator
        );
        $authorizationService = $this->createAuthorizationService(
            $permission,
            $sector,
            $userSectorPermission,
            $userRepository
        );
        $userService = new UserService(
            $userRepository,
            $authenticationService,
            $authorizationService,
            $encrypter
        );
        return $userService;
    }

    /*
    ----------------
    | Insert Tests |
    ----------------
    */

    public function testIfAValidUserWithValidTokenIsInserted(): void
    {
        $this->expectNotToPerformAssertions();

        $user = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::create("password123"),
            true
        );
        $encodedToken = $this->createEncodedToken("potato");
        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $decodedToken = $this->createDecodedToken(
            $user,
            $now,
            $now->modify("+1 hour")
        );
        $userRepository = $this->createUserRepository(
            $user
        );
        $encrypter = $this->createEncrypter(
            "potato"
        );
        $userService = $this->createUserService(
            $userRepository,
            $tokenCache,
            $this->createAuthenticationTokenDecoderWithReturn(
                $decodedToken
            ),
            $encrypter,
            $this->createAuthenticationTokenValidator(
                $clock
            )
        );
        $userService->insert(
            $user,
            $encodedToken
        );
    }

    public function testIfAValidUserWithValidTokenFailsOnInsertionBecauseOfDuplicatedUsernameOnRepository(): void
    {
        $this->expectException(RepositoryDuplicatedRegisterException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::create("password123"),
            true
        );
        $encodedToken = $this->createEncodedToken("potato");
        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $decodedToken = $this->createDecodedToken(
            $user,
            $now,
            $now->modify("+1 hour")
        );
        $userRepository = $this->createUserRepositoryThatThrowsDuplicatedUsernameException(
            $user
        );
        $encrypter = $this->createEncrypter(
            "potato"
        );
        $userService = $this->createUserService(
            $userRepository,
            $tokenCache,
            $this->createAuthenticationTokenDecoderWithReturn(
                $decodedToken
            ),
            $encrypter,
            $this->createAuthenticationTokenValidator(
                $clock
            )
        );
        $userService->insert(
            $user,
            $encodedToken
        );
    }

    public function testIfAValidUserWithValidTokenFailsOnInsertionBecauseOfEncryptionError(): void
    {
        $this->expectException(EncryptionException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("marcus"),
            DecodedPassword::create("password123"),
            true
        );
        $encodedToken = $this->createEncodedToken("potato");
        $tokenCache = $this->createTokenCache(
            $encodedToken
        );
        $clock = $this->createClock("UTC");
        $now = $clock->now();
        $decodedToken = $this->createDecodedToken(
            $user,
            $now,
            $now->modify("+1 hour")
        );
        $userRepository = $this->createUserRepository(
            $user
        );
        $encrypter = $this->createEncrypterThatThrowsExceptionOnEncrypt(
            "potato"
        );
        $userService = $this->createUserService(
            $userRepository,
            $tokenCache,
            $this->createAuthenticationTokenDecoderWithReturn(
                $decodedToken
            ),
            $encrypter,
            $this->createAuthenticationTokenValidator(
                $clock
            )
        );
        $userService->insert(
            $user,
            $encodedToken
        );
    }
}
