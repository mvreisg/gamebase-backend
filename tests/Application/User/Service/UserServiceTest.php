<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\User\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\User\Service\UserService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\Exception\EncryptionInterfaceException;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;
use Mvreisg\GamebaseBackend\Domain\User\Entity\Collection\UserCollection;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\Exception\DuplicatedUsernameException;
use Mvreisg\GamebaseBackend\Domain\User\Exception\UserNotFoundException;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\Service\UserDomainService;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private function createUser(
        Id $id,
        Username $username,
        Password $password,
        bool $isActive
    ): User {
        return User::create(
            $id,
            $username,
            $password,
            $isActive
        );
    }

    private function createUserRepository(
        bool $exists,
        bool $duplicatedUsernames,
        User $user
    ): MockObject&UserRepositoryInterface {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository
            ->method("checkIfExists")
            ->willReturn(
                $exists
            );
        $repository
            ->method("insert")
            ->willReturn($user);
        $repository
            ->method("update")
            ->willReturn(true);
        $repository
            ->method("setIsActive")
            ->willReturn(true);
        $repository
            ->method("checkDuplicatedUsernames")
            ->willReturn($duplicatedUsernames);
        $repository
            ->method("findById")
            ->willReturn($user);
        $repository
            ->method("findByUsername")
            ->willReturn($user);
        $repository
            ->method("findAll")
            ->willReturn(
                new UserCollection([
                    $user
                ])
            );

        return $repository;
    }

    private function createSector(
        Id $id,
        Name $name,
        SectorValue $value,
        bool $isActive
    ): Sector {
        return Sector::create(
            $id,
            $name,
            $value,
            $isActive
        );
    }

    private function createPermission(
        Id $id,
        Name $name,
        PermissionValue $value,
        bool $isActive
    ): Permission {
        return Permission::create(
            $id,
            $name,
            $value,
            $isActive
        );
    }

    private function createUserSectorPermissionRepository(
        UserSectorPermissionCollection $collection
    ): MockObject&UserSectorPermissionRepositoryInterface {
        $repository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $repository
            ->method("findAllByUserId")
            ->willReturn(
                $collection
            );
        return $repository;
    }

    private function createEncrypter(
        string $encryptedMessage
    ): MockObject&EncryptionInterface {
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("encrypt")
            ->willReturn(
                $encryptedMessage
            );

        return $encrypter;
    }

    private function createEncrypterWithDecryptionError(): MockObject&EncryptionInterface
    {
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("decrypt")
            ->willThrowException(
                new EncryptionInterfaceException(
                    "decryption error"
                )
            );

        return $encrypter;
    }

    private function createEncrypterWithEncryptionError(): MockObject&EncryptionInterface
    {
        $encrypter = $this->createMock(EncryptionInterface::class);
        $encrypter
            ->method("encrypt")
            ->willThrowException(
                new EncryptionInterfaceException(
                    "ecryption error"
                )
            );

        return $encrypter;
    }

    private function createTokenCacheInterface(
        bool $exists,
        string $encodedToken
    ): MockObject&AuthenticationTokenCacheInterface {
        $tokenCache = $this->createMock(AuthenticationTokenCacheInterface::class);
        $tokenCache
            ->method("exists")
            ->willReturn(
                $exists
            );
        $tokenCache
            ->method("get")
            ->willReturn(
                $encodedToken
            );

        return $tokenCache;
    }

    private function createTokenProvider(): MockObject&AuthenticationTokenProvider
    {
        $tokenProvider = $this->createMock(AuthenticationTokenProvider::class);
        return $tokenProvider;
    }

    private function createAuthenticationService(
        MockObject&AuthenticationTokenCacheInterface $tokenCache,
        MockObject&AuthenticationTokenProvider $tokenProvider
    ): AuthenticationService {
        $service = new AuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        return $service;
    }

    private function createUserDomainService(
        MockObject&UserRepositoryInterface $userRepository
    ): UserDomainService {
        $service = new UserDomainService(
            $userRepository
        );
        return $service;
    }

    private function createAuthorizationDomainService(): AuthorizationDomainService
    {
        $service = new AuthorizationDomainService();
        return $service;
    }

    private function createCheckAuthorizationUseCase(
        UserDomainService $userDomainService,
        MockObject&UserSectorPermissionRepositoryInterface $userSectorPermissionRepository,
        AuthenticationService $authenticationService,
        AuthorizationDomainService $authorizationDomainService
    ): CheckAuthorizationUseCase {
        $useCase = new CheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        return $useCase;
    }

    private function createUserService(
        MockObject&UserRepositoryInterface $userRepository,
        MockObject&EncryptionInterface $encrypter,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        UserDomainService $userDomainService
    ): UserService {
        $userService = new UserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );
        return $userService;
    }

    /*
    ----------------
    | Insert Tests |
    ----------------
    */

    public function testIfAUserGetsInserted(): void
    {
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $insertedUser = $userService->insert(
            $user,
            $encodedToken
        );

        $this->assertEquals(
            $user->getId()->getValue(),
            $insertedUser->getId()->getValue()
        );

        $this->assertEquals(
            $user->getUsername()->getValue(),
            $insertedUser->getUsername()->getValue()
        );

        $this->assertEquals(
            $user->getPassword()->getValue(),
            $insertedUser->getPassword()->getValue()
        );

        $this->assertEquals(
            $user->getIsActive(),
            $insertedUser->getIsActive()
        );
    }

    public function testIfUserInsertionFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $insertedUser = $userService->insert(
            $user,
            $encodedToken
        );

        $this->assertEquals(
            $user->getId()->getValue(),
            $insertedUser->getId()->getValue()
        );

        $this->assertEquals(
            $user->getUsername()->getValue(),
            $insertedUser->getUsername()->getValue()
        );

        $this->assertEquals(
            $user->getPassword()->getValue(),
            $insertedUser->getPassword()->getValue()
        );

        $this->assertEquals(
            $user->getIsActive(),
            $insertedUser->getIsActive()
        );
    }

    public function testIfUserInsertionFailsBecauseOfDuplicatedUsernameOnRepository(): void
    {
        $this->expectException(DuplicatedUsernameException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            true,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $userService->insert(
            $user,
            $encodedToken
        );
    }

    public function testIfUserInsertionFailsBecauseOfEncryptionError(): void
    {
        $this->expectException(EncryptionInterfaceException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypterWithEncryptionError();
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $userService->insert(
            $user,
            $encodedToken
        );
    }

    /*
    ----------------
    | Update Tests |
    ----------------
    */

    public function testIfAValidUserGetsUpdated(): void
    {
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $wasUpdated = $userService->update(
            $user,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfUserUpdateFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $userService->update(
            $user,
            $encodedToken
        );
    }

    public function testIfUserUpdateFailsBecauseOfUnexistantUserOnRepository(): void
    {
        $this->expectException(UserNotFoundException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            false,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $userService->update(
            $user,
            $encodedToken
        );
    }

    public function testIfUserUpdateFailsBecauseOfDuplicatedUsernameOnRepository(): void
    {
        $this->expectException(DuplicatedUsernameException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            true,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $userService->update(
            $user,
            $encodedToken
        );
    }

    public function testIfUserUpdateFailsBecauseOfDecryptionError(): void
    {
        $this->expectException(EncryptionInterfaceException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypterWithDecryptionError();
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $userService->update(
            $user,
            $encodedToken
        );
    }

    public function testIfUserUpdateFailsBecauseOfEncryptionError(): void
    {
        $this->expectException(EncryptionInterfaceException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypterWithEncryptionError();
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $userService->update(
            $user,
            $encodedToken
        );
    }

    /*
    -----------------------
    | Set Is Active Tests |
    -----------------------
    */

    public function testIfUserGetsSetToActive(): void
    {
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            false
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
            PermissionValue::from(PermissionType::Activate),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $isActive = true;
        $wasUpdated = $userService->setIsActive(
            $user->getId(),
            $isActive,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfUserGetsSetToInactive(): void
    {
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
            PermissionValue::from(PermissionType::Activate),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $isActive = false;
        $wasUpdated = $userService->setIsActive(
            $user->getId(),
            $isActive,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfUserActivationFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $isActive = false;
        $userService->setIsActive(
            $user->getId(),
            $isActive,
            $encodedToken
        );
    }

    public function testIfUserActivationFailsBecauseOfUnexistantUserOnRepository(): void
    {
        $this->expectException(UserNotFoundException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            false,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $isActive = false;
        $userService->setIsActive(
            $user->getId(),
            $isActive,
            $encodedToken
        );
    }

    /*
    --------------------
    | Find By Id Tests |
    --------------------
    */

    public function testIfUserGetsFoundById(): void
    {
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            false
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("List"),
            PermissionValue::from(PermissionType::List),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $foundUser = $userService->findById(
            $user->getId(),
            $encodedToken
        );

        $this->assertEquals(
            $user->getId(),
            $foundUser->getId()
        );

        $this->assertEquals(
            $user->getUsername(),
            $foundUser->getUsername()
        );

        $this->assertEquals(
            $user->getPassword(),
            $foundUser->getPassword()
        );

        $this->assertEquals(
            $user->getIsActive(),
            $foundUser->getIsActive()
        );
    }

    public function testIfUserFindByIdFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            false
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $userService->findById(
            $user->getId(),
            $encodedToken
        );
    }

    /*
    --------------------------
    | Find By Username Tests |
    --------------------------
    */

    public function testIfUserGetsFoundByUsername(): void
    {
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            false
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("List"),
            PermissionValue::from(PermissionType::List),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $foundUser = $userService->findByUsername(
            $user->getUsername(),
            $encodedToken
        );

        $this->assertEquals(
            $user->getId(),
            $foundUser->getId()
        );

        $this->assertEquals(
            $user->getUsername(),
            $foundUser->getUsername()
        );

        $this->assertEquals(
            $user->getPassword(),
            $foundUser->getPassword()
        );

        $this->assertEquals(
            $user->getIsActive(),
            $foundUser->getIsActive()
        );
    }

    public function testIfUserFindByUsernameFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            false
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $userService->findByUsername(
            $user->getUsername(),
            $encodedToken
        );
    }

    /*
    ------------------
    | Find All Tests |
    ------------------
    */

    public function testIfAllUsersGetsFound(): void
    {
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            false
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("List"),
            PermissionValue::from(PermissionType::List),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $users = $userService->findAll(
            $encodedToken
        );

        $this->assertCount(
            1,
            $users->fetchAll()
        );
    }

    public function testIfAllUsersFindFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            false
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $encodedToken = "potato";
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $encrypter = $this->createEncrypter(
            "test"
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            new UserSectorPermissionCollection([
                UserSectorPermission::create(
                    Id::create(1),
                    $user,
                    $sector,
                    $permission
                )
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $tokenProvider = $this->createTokenProvider();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $userService = $this->createUserService(
            $userRepository,
            $encrypter,
            $checkAuthorizationUseCase,
            $userDomainService
        );

        $userService->findAll(
            $encodedToken
        );
    }
}
