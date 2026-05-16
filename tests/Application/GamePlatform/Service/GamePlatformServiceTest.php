<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\GamePlatform\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Service\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\GamePlatform\Service\GamePlatformService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Collection\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\Game\Exception\GameNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Game\Repository\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Game\Service\GameDomainService;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\Collection\GamePlatformCollection;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Exception\GamePlatformNotFoundException;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Repository\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Service\GamePlatformDomainService;
use Mvreisg\GamebaseBackend\Domain\Platform\Service\PlatformDomainService;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Platform\Exception\PlatformNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Platform\Repository\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
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
use Psr\Log\NullLogger;

class GamePlatformServiceTest extends TestCase
{
    private function createGame(
        Id $id,
        Name $name,
        bool $isActive
    ): Game {
        return Game::create(
            $id,
            $name,
            $isActive
        );
    }

    private function createGamePlatform(
        Id $id,
        Id $gameId,
        Id $platformId,
    ): GamePlatform {
        return GamePlatform::create(
            $id,
            Game::createFromIdOnly(
                $gameId
            ),
            Platform::createFromIdOnly(
                $platformId
            ),
        );
    }

    private function createGameRepository(
        bool $exists,
        bool $duplicatedGameNames,
        Game $game
    ): MockObject&GameRepositoryInterface {
        $repository = $this->createMock(GameRepositoryInterface::class);
        $repository
            ->method("checkIfExists")
            ->willReturn(
                $exists
            );
        $repository
            ->method("insert")
            ->willReturn($game);
        $repository
            ->method("update")
            ->willReturn(true);
        $repository
            ->method("setIsActive")
            ->willReturn(true);
        $repository
            ->method("findById")
            ->willReturn($game);
        $repository
            ->method("findAll")
            ->willReturn(
                new GameCollection([
                    $game
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
            $tokenProvider,
            new NullLogger()
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
            $authorizationDomainService,
            new NullLogger()
        );
        return $useCase;
    }

    private function createPlatformRepository(
        bool $exists
    ): MockObject&PlatformRepositoryInterface {
        $repository = $this->createMock(PlatformRepositoryInterface::class);
        $repository
            ->method("checkIfExists")
            ->willReturn($exists);

        return $repository;
    }

    private function createGamePlatformRepository(
        bool $exists,
        bool $isDeleting,
        GamePlatform $gamePlatform
    ): MockObject&GamePlatformRepositoryInterface {
        $repository = $this->createMock(GamePlatformRepositoryInterface::class);
        $repository
            ->method("insert")
            ->willReturn($gamePlatform);
        $repository
            ->method("update")
            ->willReturn(true);
        $repository
            ->method("delete")
            ->willReturn($isDeleting);
        $repository
            ->method("findById")
            ->willReturn($gamePlatform);
        $repository
            ->method("findAll")
            ->willReturn(
                new GamePlatformCollection([
                    $gamePlatform
                ])
            );
        $repository
            ->method("checkIfExists")
            ->willReturn($exists);
        return $repository;
    }

    private function createGameDomainService(
        MockObject&GameRepositoryInterface $gameRepository
    ): GameDomainService {
        $service = new GameDomainService(
            $gameRepository
        );
        return $service;
    }

    private function createPlatformDomainService(
        MockObject&PlatformRepositoryInterface $genreRepository
    ): PlatformDomainService {
        $service = new PlatformDomainService(
            $genreRepository
        );
        return $service;
    }

    private function createGamePlatformDomainService(
        MockObject&GamePlatformRepositoryInterface $gamePlatformRepository
    ): GamePlatformDomainService {
        $service = new GamePlatformDomainService(
            $gamePlatformRepository
        );
        return $service;
    }

    private function createGamePlatformService(
        MockObject&GamePlatformRepositoryInterface $gamePlatformRepository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
        PlatformDomainService $genreDomainService,
        GamePlatformDomainService $gamePlatformDomainService
    ): GamePlatformService {
        $service = new GamePlatformService(
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService,
            $gamePlatformRepository,
            new NullLogger()
        );
        return $service;
    }

    /*
    ----------------
    | Insert Tests |
    ----------------
    */

    public function testIfAGamePlatformGetsInserted(): void
    {
        $encodedToken = "potato";
        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("GamePlatform"),
            SectorValue::from(SectorType::GamePlatform),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            true,
            false,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $insertedGamePlatform = $gamePlatformService->insert(
            $gamePlatform,
            $encodedToken
        );

        $this->assertEquals(
            $gamePlatform->getId()->getValue(),
            $insertedGamePlatform->getId()->getValue()
        );

        $this->assertEquals(
            $gamePlatform->getGame()->getId()->getValue(),
            $insertedGamePlatform->getGame()->getId()->getValue()
        );

        $this->assertEquals(
            $gamePlatform->getPlatform()->getId()->getValue(),
            $insertedGamePlatform->getPlatform()->getId()->getValue()
        );
    }

    public function testIfGamePlatformInsertionFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            true,
            false,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->insert(
            $gamePlatform,
            $encodedToken
        );
    }

    public function testIfGamePlatformInsertionFailsBecauseOfUnexistantGame(): void
    {
        $this->expectException(GameNotFoundException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            true,
            $game
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("GamePlatform"),
            SectorValue::from(SectorType::GamePlatform),
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
        $gameRepository = $this->createGameRepository(
            false,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            true,
            false,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->insert(
            $gamePlatform,
            $encodedToken
        );
    }

    public function testIfGamePlatformInsertionFailsBecauseOfUnexistantPlatform(): void
    {
        $this->expectException(PlatformNotFoundException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            true,
            $game
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("GamePlatform"),
            SectorValue::from(SectorType::GamePlatform),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            false
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            true,
            false,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->insert(
            $gamePlatform,
            $encodedToken
        );
    }

    /*
    ----------------
    | Update Tests |
    ----------------
    */

    public function testIfAValidGamePlatformGetsUpdated(): void
    {
        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
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
            SectorValue::from(SectorType::GamePlatform),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            true,
            false,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $wasUpdated = $gamePlatformService->update(
            $gamePlatform,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfGamePlatformUpdateFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
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
            SectorValue::from(SectorType::Game),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            true,
            false,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->update(
            $gamePlatform,
            $encodedToken
        );
    }

    public function testIfGamePlatformUpdateFailsBecauseOfUnexistantGameOnRepository(): void
    {
        $this->expectException(GameNotFoundException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            false,
            false,
            $game
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
            SectorValue::from(SectorType::GamePlatform),
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
        $gameRepository = $this->createGameRepository(
            false,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            true,
            false,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->update(
            $gamePlatform,
            $encodedToken
        );
    }

    public function testIfGamePlatformUpdateFailsBecauseOfUnexistantPlatformOnRepository(): void
    {
        $this->expectException(PlatformNotFoundException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            false,
            false,
            $game
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
            SectorValue::from(SectorType::GamePlatform),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            false
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            true,
            false,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->update(
            $gamePlatform,
            $encodedToken
        );
    }

    public function testIfGamePlatformUpdateFailsBecauseOfUnexistantGamePlatformOnRepository(): void
    {
        $this->expectException(GamePlatformNotFoundException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            false,
            false,
            $game
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
            SectorValue::from(SectorType::GamePlatform),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            false,
            false,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->update(
            $gamePlatform,
            $encodedToken
        );
    }

    /*
    ----------------
    | Delete Tests |
    ----------------
    */

    public function testIfGameGetsDeleted(): void
    {
        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            false
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
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
            SectorValue::from(SectorType::GamePlatform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
            PermissionValue::from(PermissionType::Delete),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            true,
            true,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $wasDeleted = $gamePlatformService->delete(
            $gamePlatform->getId(),
            $encodedToken
        );

        $this->assertTrue(
            $wasDeleted
        );
    }

    public function testIfGamePlatformDeletionFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            false
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
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
            SectorValue::from(SectorType::Game),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            false,
            true,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->delete(
            $gamePlatform->getId(),
            $encodedToken
        );
    }

    public function testIfGamePlatformDeletionFailsBecauseOfUnexistantGamePlatformOnRepository(): void
    {
        $this->expectException(GamePlatformNotFoundException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            false
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            false,
            false,
            $game
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
            SectorValue::from(SectorType::GamePlatform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
            PermissionValue::from(PermissionType::Delete),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            false,
            true,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->delete(
            $gamePlatform->getId(),
            $encodedToken
        );
    }

    /*
    --------------------
    | Find By Id Tests |
    --------------------
    */

    public function testIfGamePlatformGetsFoundById(): void
    {
        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
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
            SectorValue::from(SectorType::GamePlatform),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            false,
            true,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $foundGamePlatform = $gamePlatformService->findById(
            $gamePlatform->getId(),
            $encodedToken
        );

        $this->assertEquals(
            $gamePlatform->getId(),
            $foundGamePlatform->getId()
        );

        $this->assertEquals(
            $gamePlatform->getGame()->getId(),
            $foundGamePlatform->getGame()->getId()
        );

        $this->assertEquals(
            $gamePlatform->getPlatform()->getId(),
            $foundGamePlatform->getPlatform()->getId()
        );
    }

    public function testIfGamePlatformFindByIdFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
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
            SectorValue::from(SectorType::Game),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            false,
            true,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->findById(
            $gamePlatform->getId(),
            $encodedToken
        );
    }

    /*
    ------------------
    | Find All Tests |
    ------------------
    */

    public function testIfAllGamePlatformsGetsFound(): void
    {
        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
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
            SectorValue::from(SectorType::GamePlatform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            false,
            true,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatforms = $gamePlatformService->findAll(
            $encodedToken
        );

        $this->assertCount(
            1,
            $gamePlatforms->fetchAll()
        );
    }

    public function testIfAllGamesFindFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $gamePlatform = $this->createGamePlatform(
            Id::create(1),
            Id::create(1),
            Id::create(1)
        );
        $game = $this->createGame(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
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
            SectorValue::from(SectorType::GamePlatform),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Activate"),
            PermissionValue::from(PermissionType::Delete),
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
        $gameRepository = $this->createGameRepository(
            true,
            false,
            $game
        );
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $genreRepository = $this->createPlatformRepository(
            true
        );
        $genreDomainService = $this->createPlatformDomainService(
            $genreRepository
        );
        $gamePlatformRepository = $this->createGamePlatformRepository(
            false,
            true,
            $gamePlatform
        );
        $gamePlatformDomainService = $this->createGamePlatformDomainService(
            $gamePlatformRepository
        );
        $gamePlatformService = $this->createGamePlatformService(
            $gamePlatformRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gamePlatformDomainService
        );

        $gamePlatformService->findAll(
            $encodedToken
        );
    }
}
