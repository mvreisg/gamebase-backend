<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Authorization\UseCase;

use Mvreisg\GamebaseBackend\Application\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\AuthenticationToken;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\ClockInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\Exception\UserNotFoundException;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\Service\UserDomainService;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\UserSectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Clock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckAuthorizationUseCaseTest extends TestCase
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

    private function createAuthenticationToken(
        AuthenticationData $data,
        \DateTimeImmutable $issuedAt,
        \DateTimeImmutable $expiresAt,
    ): MockObject&AuthenticationToken {
        $token = $this->createMock(AuthenticationToken::class);

        $token
            ->method("getIssuedAt")
            ->willReturn($issuedAt);

        $token
            ->method("getExpiresAt")
            ->willReturn($expiresAt);

        $token
            ->method("getAuthenticationData")
            ->willReturn($data);

        return $token;
    }

    private function createUser(
        Id $id,
        Username $username,
        DecodedPassword $password,
        bool $isActive
    ): User {
        $user = User::create(
            $id,
            $username,
            $password,
            $isActive
        );
        return $user;
    }

    private function createSector(
        Id $id,
        Name $name,
        SectorValue $value,
        bool $isActive,
    ): Sector {
        $sector = Sector::create(
            $id,
            $name,
            $value,
            $isActive
        );
        return $sector;
    }

    private function createPermission(
        Id $id,
        Name $name,
        PermissionValue $value,
        bool $isActive,
    ): Permission {
        $permission = Permission::create(
            $id,
            $name,
            $value,
            $isActive
        );
        return $permission;
    }

    private function createUserRepository(
        User $user
    ): MockObject&UserRepositoryInterface {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository
            ->method("findById")
            ->willReturn(
                $user
            );

        $repository
            ->method("checkIfExists")
            ->willReturn(
                true
            );

        return $repository;
    }

    private function createUserRepositoryWithUnexistantUsers(): MockObject&UserRepositoryInterface
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository
            ->method("checkIfExists")
            ->willReturn(
                false
            );

        return $repository;
    }

    private function createPermissionRepository(
        Permission $permission
    ): MockObject&PermissionRepositoryInterface {
        $permissionRepository = $this->createMock(PermissionRepositoryInterface::class);
        $permissionRepository
            ->method("findById")
            ->willReturn(
                $permission
            );
        return $permissionRepository;
    }

    private function createSectorRepository(
        Sector $sector
    ): MockObject&SectorRepositoryInterface {
        $sectorRepository = $this->createMock(SectorRepositoryInterface::class);
        $sectorRepository
            ->method("findById")
            ->willReturn(
                $sector
            );
        return $sectorRepository;
    }

    private function createUserSectorPermission(
        ?Id $id,
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

    private function createAuthenticationTokenCache(
        string $token
    ): MockObject&AuthenticationTokenCacheInterface {
        $cache = $this->createMock(AuthenticationTokenCacheInterface::class);
        $cache
            ->method("exists")
            ->willReturn(true);
        $cache
            ->method("get")
            ->willReturn(
                $token
            );
        return $cache;
    }

    private function createAuthenticationTokenProvider(
        AuthenticationToken $token,
    ): MockObject&AuthenticationTokenProvider {
        $provider = $this->createMock(AuthenticationTokenProvider::class);
        $provider
            ->method("decode")
            ->willReturn($token);

        return $provider;
    }

    private function createAuthenticationService(
        MockObject&AuthenticationTokenCacheInterface $tokenCache,
        MockObject&AuthenticationTokenProvider $tokenProvider
    ): AuthenticationService {
        return new AuthenticationService(
            $tokenCache,
            $tokenProvider,
        );
    }

    private function createCheckAuthorizationUseCase(
        UserDomainService $userDomainService,
        AuthorizationDomainService $authorizationDomainService,
        MockObject&UserSectorPermissionRepositoryInterface $repository,
        AuthenticationService $authenticationService
    ): CheckAuthorizationUseCase {
        return new CheckAuthorizationUseCase(
            $userDomainService,
            $repository,
            $authenticationService,
            $authorizationDomainService
        );
    }

    /*
    ---------------
    | Check Tests |
    ---------------
    */

    public function testIfAUserHaveTheCorrectAuthorizations(): void
    {
        $this->expectNotToPerformAssertions();

        $user = $this->createUser(
            Id::create(1),
            new Username("test"),
            new DecodedPassword("password"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            new Name("User"),
            SectorValue::from(
                SectorType::User
            ),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            new Name("Create"),
            PermissionValue::from(
                PermissionType::Create
            ),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userSectorPermissionCollection = new UserSectorPermissionCollection([
            $userSectorPermission
        ]);

        $userRepository = $this->createUserRepository(
            $user
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );
        $userDomainService = new UserDomainService(
            $userRepository
        );
        $encodedToken = "potato";
        $tokenCache = $this->createAuthenticationTokenCache(
            $encodedToken
        );
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $authenticationData = new AuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $decodedToken = $this->createAuthenticationToken(
            $authenticationData,
            $issuedAt,
            $expiresAt
        );
        $tokenProvider = $this->createAuthenticationTokenProvider(
            $decodedToken
        );
        $authorizationDomainService = new AuthorizationDomainService();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $authorizationDomainService,
            $userSectorPermissionRepository,
            $authenticationService
        );

        $checkAuthorizationUseCase->execute(
            $encodedToken,
            SectorType::User,
            PermissionType::Create
        );
    }

    public function testIfAUnexistantUserIdTriesToCheckAuthorization(): void
    {
        $this->expectException(UserNotFoundException::class);

        $user = $this->createUser(
            Id::create(1),
            new Username("test"),
            new DecodedPassword("password"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            new Name("User"),
            SectorValue::from(
                SectorType::User
            ),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            new Name("Create"),
            PermissionValue::from(
                PermissionType::Create
            ),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userSectorPermissionCollection = new UserSectorPermissionCollection([
            $userSectorPermission
        ]);

        $userRepository = $this->createUserRepositoryWithUnexistantUsers();
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );
        $userDomainService = new UserDomainService(
            $userRepository
        );
        $encodedToken = "potato";
        $tokenCache = $this->createAuthenticationTokenCache(
            $encodedToken
        );
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $authenticationData = new AuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $decodedToken = $this->createAuthenticationToken(
            $authenticationData,
            $issuedAt,
            $expiresAt
        );
        $tokenProvider = $this->createAuthenticationTokenProvider(
            $decodedToken
        );
        $authorizationDomainService = new AuthorizationDomainService();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $authorizationDomainService,
            $userSectorPermissionRepository,
            $authenticationService
        );

        $checkAuthorizationUseCase->execute(
            $encodedToken,
            SectorType::User,
            PermissionType::Create
        );
    }

    public function testIfAUserDoNotHaveTheCorrectSectorAuthorization(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            new Username("test"),
            new DecodedPassword("password"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            new Name("User"),
            SectorValue::from(
                SectorType::User
            ),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            new Name("Create"),
            PermissionValue::from(
                PermissionType::Create
            ),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userSectorPermissionCollection = new UserSectorPermissionCollection([
            $userSectorPermission
        ]);

        $userRepository = $this->createUserRepository(
            $user
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );
        $userDomainService = new UserDomainService(
            $userRepository
        );
        $encodedToken = "potato";
        $tokenCache = $this->createAuthenticationTokenCache(
            $encodedToken
        );
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $authenticationData = new AuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $decodedToken = $this->createAuthenticationToken(
            $authenticationData,
            $issuedAt,
            $expiresAt
        );
        $tokenProvider = $this->createAuthenticationTokenProvider(
            $decodedToken
        );
        $authorizationDomainService = new AuthorizationDomainService();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $authorizationDomainService,
            $userSectorPermissionRepository,
            $authenticationService
        );

        $checkAuthorizationUseCase->execute(
            $encodedToken,
            SectorType::Game,
            PermissionType::Create
        );
    }

    public function testIfAUserDoNotHaveTheCorrectPermissionAuthorization(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            new Username("test"),
            new DecodedPassword("password"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            new Name("User"),
            SectorValue::from(
                SectorType::User
            ),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            new Name("Create"),
            PermissionValue::from(
                PermissionType::Create
            ),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user,
            $sector,
            $permission
        );
        $userSectorPermissionCollection = new UserSectorPermissionCollection([
            $userSectorPermission
        ]);

        $userRepository = $this->createUserRepository(
            $user
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );
        $userDomainService = new UserDomainService(
            $userRepository
        );
        $encodedToken = "potato";
        $tokenCache = $this->createAuthenticationTokenCache(
            $encodedToken
        );
        $clock = $this->createClock("UTC");
        $issuedAt = $clock->now();
        $expiresAt = $issuedAt->modify("+1 day");
        $authenticationData = new AuthenticationData(
            $user->getId(),
            $user->getUsername()
        );
        $decodedToken = $this->createAuthenticationToken(
            $authenticationData,
            $issuedAt,
            $expiresAt
        );
        $tokenProvider = $this->createAuthenticationTokenProvider(
            $decodedToken
        );
        $authorizationDomainService = new AuthorizationDomainService();
        $authenticationService = $this->createAuthenticationService(
            $tokenCache,
            $tokenProvider
        );
        $checkAuthorizationUseCase = $this->createCheckAuthorizationUseCase(
            $userDomainService,
            $authorizationDomainService,
            $userSectorPermissionRepository,
            $authenticationService
        );

        $checkAuthorizationUseCase->execute(
            $encodedToken,
            SectorType::User,
            PermissionType::List
        );
    }
}
