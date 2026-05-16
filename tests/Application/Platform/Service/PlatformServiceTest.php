<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Platform\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Service\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\Platform\Service\PlatformService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Collection\PlatformCollection;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Platform\Exception\PlatformNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Platform\Service\PlatformDomainService;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\DuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;
use Mvreisg\GamebaseBackend\Domain\User\Entity\Collection\UserCollection;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
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

class PlatformServiceTest extends TestCase
{
    private function createPlatform(
        Id $id,
        Name $name,
        bool $isActive
    ): Platform {
        return Platform::create(
            $id,
            $name,
            $isActive
        );
    }

    private function createPlatformRepository(
        bool $exists,
        bool $duplicatedGameNames,
        Platform $platform
    ): MockObject&PlatformRepositoryInterface {
        $repository = $this->createMock(PlatformRepositoryInterface::class);
        $repository
            ->method("checkIfExists")
            ->willReturn(
                $exists
            );
        $repository
            ->method("insert")
            ->willReturn($platform);
        $repository
            ->method("update")
            ->willReturn(true);
        $repository
            ->method("setIsActive")
            ->willReturn(true);
        $repository
            ->method("findById")
            ->willReturn($platform);
        $repository
            ->method("findAll")
            ->willReturn(
                new PlatformCollection([
                    $platform
                ])
            );
        $repository
            ->method("checkDuplicatedNames")
            ->willReturn($duplicatedGameNames);

        return $repository;
    }

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

    private function createPlatformDomainService(
        MockObject&PlatformRepositoryInterface $platformRepository
    ): PlatformDomainService {
        $service = new PlatformDomainService(
            $platformRepository
        );
        return $service;
    }

    private function createPlatformService(
        MockObject&PlatformRepositoryInterface $platformRepository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        PlatformDomainService $platformDomainService
    ): PlatformService {
        $platformService = new PlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );
        return $platformService;
    }

    /*
    ----------------
    | Insert Tests |
    ----------------
    */

    public function testIfAPlatformGetsInserted(): void
    {
        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Game"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $insertedPlatform = $platformService->insert(
            $platform,
            $encodedToken
        );

        $this->assertEquals(
            $platform->getId()->getValue(),
            $insertedPlatform->getId()->getValue()
        );

        $this->assertEquals(
            $platform->getName()->getValue(),
            $insertedPlatform->getName()->getValue()
        );

        $this->assertEquals(
            $platform->getIsActive(),
            $insertedPlatform->getIsActive()
        );
    }

    public function testIfPlatformInsertionFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
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
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $platformService->insert(
            $platform,
            $encodedToken
        );
    }

    public function testIfPlatformInsertionFailsBecauseOfDuplicatedNameOnRepository(): void
    {
        $this->expectException(DuplicatedNameException::class);

        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            true,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $platformService->insert(
            $platform,
            $encodedToken
        );
    }

    /*
    ----------------
    | Update Tests |
    ----------------
    */

    public function testIfAValidPlatformGetsUpdated(): void
    {
        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $wasUpdated = $platformService->update(
            $platform,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfPlatformUpdateFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("List"),
            PermissionValue::from(PermissionType::List),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $platformService->update(
            $platform,
            $encodedToken
        );
    }

    public function testIfPlatformUpdateFailsBecauseOfUnexistantPlatformOnRepository(): void
    {
        $this->expectException(PlatformNotFoundException::class);

        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            false,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $platformService->update(
            $platform,
            $encodedToken
        );
    }

    public function testIfPlatformUpdateFailsBecauseOfDuplicatedNameOnRepository(): void
    {
        $this->expectException(DuplicatedNameException::class);

        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            true,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $platformService->update(
            $platform,
            $encodedToken
        );
    }

    /*
    -----------------------
    | Set Is Active Tests |
    -----------------------
    */

    public function testIfPlatformGetsSetToActive(): void
    {
        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            false
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
            PermissionValue::from(PermissionType::Activate),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $isActive = true;
        $wasUpdated = $platformService->setIsActive(
            $platform->getId(),
            $isActive,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfPlatformGetsSetToInactive(): void
    {
        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
            PermissionValue::from(PermissionType::Activate),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $isActive = false;
        $wasUpdated = $platformService->setIsActive(
            $platform->getId(),
            $isActive,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfPlatformActivationFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            false
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("List"),
            PermissionValue::from(PermissionType::List),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $isActive = true;
        $platformService->setIsActive(
            $platform->getId(),
            $isActive,
            $encodedToken
        );
    }

    public function testIfPlatformActivationFailsBecauseOfUnexistantPlatformOnRepository(): void
    {
        $this->expectException(PlatformNotFoundException::class);

        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            false
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            false,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
            PermissionValue::from(PermissionType::Activate),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $isActive = true;
        $platformService->setIsActive(
            $platform->getId(),
            $isActive,
            $encodedToken
        );
    }

    /*
    --------------------
    | Find By Id Tests |
    --------------------
    */

    public function testIfPlatformGetsFoundById(): void
    {
        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("List"),
            PermissionValue::from(PermissionType::List),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $foundPlatform = $platformService->findById(
            $platform->getId(),
            $encodedToken
        );

        $this->assertEquals(
            $platform->getId(),
            $foundPlatform->getId()
        );

        $this->assertEquals(
            $platform->getName(),
            $foundPlatform->getName()
        );

        $this->assertEquals(
            $platform->getIsActive(),
            $foundPlatform->getIsActive()
        );
    }

    public function testIfPlatformFindByIdFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
            PermissionValue::from(PermissionType::Activate),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $platformService->findById(
            $platform->getId(),
            $encodedToken
        );
    }

    /*
    ------------------
    | Find All Tests |
    ------------------
    */

    public function testIfAllPlatformsGetsFound(): void
    {
        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("List"),
            PermissionValue::from(PermissionType::List),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $platforms = $platformService->findAll(
            $encodedToken
        );

        $this->assertCount(
            1,
            $platforms->fetchAll()
        );
    }

    public function testIfAllPlatformsFindFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $platform = $this->createPlatform(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $platformRepository = $this->createPlatformRepository(
            true,
            false,
            $platform
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Platform"),
            SectorValue::from(SectorType::Platform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
            PermissionValue::from(PermissionType::Activate),
            true
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
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
        $platformDomainService = $this->createPlatformDomainService(
            $platformRepository
        );
        $platformService = $this->createPlatformService(
            $platformRepository,
            $checkAuthorizationUseCase,
            $platformDomainService
        );

        $platformService->findAll(
            $encodedToken
        );
    }
}
