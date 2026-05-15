<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\UserSectorPermission\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\AuthenticationToken;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service\UserSectorPermissionService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\Exception\PermissionNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Permission\Service\PermissionDomainService;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\Exception\SectorNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Sector\Service\SectorDomainService;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;
use Mvreisg\GamebaseBackend\Domain\User\Entity\Collection\UserCollection;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\Exception\UserNotFoundException;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\Service\UserDomainService;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Password;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Exception\UserSectorPermissionNotFoundException;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Service\UserSectorPermissionDomainService;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Clock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserSectorPermissionServiceTest extends TestCase
{
    private function createClock(): ClockInterface
    {
        $timezone = new \DateTimeZone("UTC");
        $clock = $this->createMock(Clock::class);
        $clock
            ->method("now")
            ->willReturn(
                new \DateTimeImmutable(
                    "now",
                    $timezone
                )
            );
        $clock
            ->method("getTimezone")
            ->willReturn(
                $timezone
            );
        return $clock;
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

    private function createUserSectorPermission(
        Id $id,
        User $user,
        Sector $sector,
        Permission $permission
    ): UserSectorPermission {
        return UserSectorPermission::create(
            $id,
            $user,
            $sector,
            $permission
        );
    }

    private function createUserSectorPermissionRepository(
        bool $exists,
        bool $wasUpdated,
        bool $wasDeleted,
        UserSectorPermission $entity,
        UserSectorPermissionCollection $collection
    ): MockObject&UserSectorPermissionRepositoryInterface {
        $repository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $repository
            ->method("insert")
            ->willReturn(
                $entity
            );
        $repository
            ->method("update")
            ->willReturn(
                $wasUpdated
            );
        $repository
            ->method("delete")
            ->willReturn(
                $wasDeleted
            );
        $repository
            ->method("findById")
            ->willReturn(
                $entity
            );
        $repository
            ->method("checkIfExists")
            ->willReturn(
                $exists
            );
        $repository
            ->method("findAll")
            ->willReturn(
                $collection
            );
        $repository
            ->method("findAllByUserId")
            ->willReturn(
                $collection
            );
        return $repository;
    }

    private function createUserSectorPermissionDomainService(
        MockObject&UserSectorPermissionRepositoryInterface $userSectorPermissionRepository
    ): UserSectorPermissionDomainService {
        $service = new UserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        return $service;
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

    private function createTokenProvider(
        ClockInterface $clock,
        User $user
    ): MockObject&AuthenticationTokenProvider {
        $tokenProvider = $this->createMock(AuthenticationTokenProvider::class);
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $tokenProvider
            ->method("decode")
            ->willReturn(
                new AuthenticationToken(
                    $issuedAt,
                    $expiresAt,
                    new AuthenticationData(
                        $user->getId(),
                        $user->getUsername()
                    )
                )
            );
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

    private function createSectorRepository(
        bool $exists
    ): MockObject&SectorRepositoryInterface {
        $repository = $this->createMock(SectorRepositoryInterface::class);
        $repository
            ->method("checkIfExists")
            ->willReturn(
                $exists
            );
        return $repository;
    }

    private function createSectorDomainService(
        MockObject&SectorRepositoryInterface $sectorRepository
    ): SectorDomainService {
        $service = new SectorDomainService(
            $sectorRepository
        );
        return $service;
    }

    private function createPermissionRepository(
        bool $exists
    ): MockObject&PermissionRepositoryInterface {
        $repository = $this->createMock(PermissionRepositoryInterface::class);
        $repository
            ->method("checkIfExists")
            ->willReturn(
                $exists
            );
        return $repository;
    }

    private function createPermissionDomainService(
        MockObject&PermissionRepositoryInterface $permissionRepository
    ): PermissionDomainService {
        $service = new PermissionDomainService(
            $permissionRepository
        );
        return $service;
    }

    private function createUserSectorPermissionService(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        UserDomainService $userDomainService,
        SectorDomainService $sectorDomainService,
        PermissionDomainService $permissionDomainService,
        UserSectorPermissionDomainService $userSectorPermissionDomainService,
        MockObject&UserSectorPermissionRepositoryInterface $userSectorPermissionRepository
    ): UserSectorPermissionService {
        $service = new UserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );
        return $service;
    }

    /*
    ----------------
    | Insert Tests |
    ----------------
    */

    public function testIfAUserSectorPermissionGetsInserted(): void
    {
        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("UserSectorPermission"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            true,
            false,
            false,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $insertedUserSectorPermission = $userSectorPermissionService->insert(
            $userSectorPermission,
            $encodedToken
        );

        $this->assertEquals(
            $userSectorPermission->getId()->getValue(),
            $insertedUserSectorPermission->getId()->getValue()
        );

        $this->assertEquals(
            $userSectorPermission->getUser()->getId()->getValue(),
            $insertedUserSectorPermission->getUser()->getId()->getValue()
        );

        $this->assertEquals(
            $userSectorPermission->getSector()->getId()->getValue(),
            $insertedUserSectorPermission->getSector()->getId()->getValue()
        );

        $this->assertEquals(
            $userSectorPermission->getPermission()->getId()->getValue(),
            $insertedUserSectorPermission->getPermission()->getId()->getValue()
        );
    }

    public function testIfUserSectorPermissionInsertionFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $encodedToken = "potato";
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
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
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
            true,
            false,
            false,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
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
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->insert(
            $userSectorPermission,
            $encodedToken
        );
    }

    public function testIfUserSectorPermissionInsertionFailsBecauseOfUnexistantUser(): void
    {
        $this->expectException(UserNotFoundException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            false,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            true,
            false,
            false,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
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
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->insert(
            $userSectorPermission,
            $encodedToken
        );
    }

    public function testIfUserSectorPermissionInsertionFailsBecauseOfUnexistantSector(): void
    {
        $this->expectException(SectorNotFoundException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
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
            true,
            false,
            false,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
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
        $sectorRepository = $this->createSectorRepository(
            false
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->insert(
            $userSectorPermission,
            $encodedToken
        );
    }

    public function testIfUserSectorPermissionInsertionFailsBecauseOfUnexistantPermission(): void
    {
        $this->expectException(PermissionNotFoundException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
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
            true,
            false,
            false,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
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
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            false
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->insert(
            $userSectorPermission,
            $encodedToken
        );
    }

    /*
    ----------------
    | Update Tests |
    ----------------
    */

    public function testIfAValidUserSectorPermissionGetsUpdated(): void
    {
        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("UserSectorPermission"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Update"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            true,
            true,
            false,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $wasUpdated = $userSectorPermissionService->update(
            $userSectorPermission,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfUserSectorPermissionUpdateFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("UserSectorPermission"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            true,
            true,
            false,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->update(
            $userSectorPermission,
            $encodedToken
        );
    }

    public function testIfUserSectorPermissionUpdateFailsBecauseOfUnexistantUserOnRepository(): void
    {
        $this->expectException(UserNotFoundException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("a"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            false,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            false,
            false,
            false,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
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
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->update(
            $userSectorPermission,
            $encodedToken
        );
    }

    public function testIfUserSectorPermissionUpdateFailsBecauseOfUnexistantSectorOnRepository(): void
    {
        $this->expectException(SectorNotFoundException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("a"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
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
            true,
            false,
            false,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
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
        $sectorRepository = $this->createSectorRepository(
            false
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->update(
            $userSectorPermission,
            $encodedToken
        );
    }

    public function testIfUserSectorPermissionUpdateFailsBecauseOfUnexistantPermissionOnRepository(): void
    {
        $this->expectException(PermissionNotFoundException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("a"),
            PermissionValue::from(PermissionType::Update),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
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
            true,
            false,
            false,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
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
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            false
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->update(
            $userSectorPermission,
            $encodedToken
        );
    }

    /*
    ----------------
    | Delete Tests |
    ----------------
    */

    public function testIfAUserSectorPermissionGetsDeleted(): void
    {
        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("UserSectorPermission"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Delete"),
            PermissionValue::from(PermissionType::Delete),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            true,
            true,
            true,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $wasDeleted = $userSectorPermissionService->delete(
            $userSectorPermission->getId(),
            $encodedToken
        );

        $this->assertTrue(
            $wasDeleted
        );
    }

    public function testIfUserSectorPermissionDeletionFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("UserSectorPermission"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            true,
            true,
            true,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->delete(
            $userSectorPermission->getId(),
            $encodedToken
        );
    }

    public function testIfUserSectorPermissionDeletionFailsBecauseOfUnexistantUserSectorPermissionOnRepository(): void
    {
        $this->expectException(UserSectorPermissionNotFoundException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("UserSectorPermission"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Delete"),
            PermissionValue::from(PermissionType::Delete),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            false,
            true,
            true,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->delete(
            $userSectorPermission->getId(),
            $encodedToken
        );
    }

    /*
    --------------------
    | Find By Id Tests |
    --------------------
    */

    public function testIfUserSectorPermissionGetsFoundById(): void
    {
        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("UserSectorPermission"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Delete"),
            PermissionValue::from(PermissionType::List),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            true,
            true,
            true,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $foundUserSectorPermission = $userSectorPermissionService->findById(
            $userSectorPermission->getId(),
            $encodedToken
        );

        $this->assertEquals(
            $userSectorPermission->getId(),
            $foundUserSectorPermission->getId()
        );

        $this->assertEquals(
            $userSectorPermission->getUser()->getId(),
            $foundUserSectorPermission->getUser()->getId()
        );

        $this->assertEquals(
            $userSectorPermission->getSector()->getId(),
            $foundUserSectorPermission->getSector()->getId()
        );

        $this->assertEquals(
            $userSectorPermission->getPermission()->getId(),
            $foundUserSectorPermission->getPermission()->getId()
        );
    }

    public function testIfUserSectorPermissionFindByIdFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("UserSectorPermission"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Delete"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            true,
            true,
            true,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->findById(
            $userSectorPermission->getId(),
            $encodedToken
        );
    }

    /*
    ------------------
    | Find All Tests |
    ------------------
    */

    public function testIfAllUserSectorPermissionsGetsFound(): void
    {
        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("UserSectorPermission"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Delete"),
            PermissionValue::from(PermissionType::List),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            true,
            true,
            true,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissions = $userSectorPermissionService->findAll(
            $encodedToken
        );

        $this->assertCount(
            1,
            $userSectorPermissions->fetchAll()
        );
    }

    public function testIfAllGamesFindFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $encodedToken = "potato";
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("UserSectorPermission"),
            SectorValue::from(SectorType::UserSectorPermission),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Delete"),
            PermissionValue::from(PermissionType::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userRepository = $this->createUserRepository(
            true,
            false,
            $user
        );
        $userDomainService = $this->createUserDomainService(
            $userRepository
        );
        $tokenCache = $this->createTokenCacheInterface(
            true,
            $encodedToken
        );
        $clock = $this->createClock();
        $tokenProvider = $this->createTokenProvider(
            $clock,
            $user
        );
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            true,
            true,
            true,
            $userSectorPermission,
            new UserSectorPermissionCollection([
                $userSectorPermission
            ])
        );
        $authorizationDomainService = $this->createAuthorizationDomainService();
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $userSectorPermissionRepository,
            $authenticationService,
            $authorizationDomainService
        );
        $sectorRepository = $this->createSectorRepository(
            true
        );
        $sectorDomainService = $this->createSectorDomainService(
            $sectorRepository
        );
        $permissionRepository = $this->createPermissionRepository(
            true
        );
        $permissionDomainService = $this->createPermissionDomainService(
            $permissionRepository
        );
        $userSectorPermissionDomainService = $this->createUserSectorPermissionDomainService(
            $userSectorPermissionRepository
        );
        $userSectorPermissionService = $this->createUserSectorPermissionService(
            $checkAuthorizationUseCase,
            $userDomainService,
            $sectorDomainService,
            $permissionDomainService,
            $userSectorPermissionDomainService,
            $userSectorPermissionRepository
        );

        $userSectorPermissionService->findAll(
            $encodedToken
        );
    }
}
