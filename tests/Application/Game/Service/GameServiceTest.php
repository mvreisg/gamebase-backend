<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Game\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\Game\Service\GameService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Collection\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\Game\Exception\GameNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Game\Repository\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Game\Service\GameDomainService;
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

class GameServiceTest extends TestCase
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

    private function createGameDomainService(
        MockObject&GameRepositoryInterface $gameRepository
    ): GameDomainService {
        $service = new GameDomainService(
            $gameRepository
        );
        return $service;
    }

    private function createGameService(
        MockObject&GameRepositoryInterface $gameRepository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService
    ): GameService {
        $gameService = new GameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );
        return $gameService;
    }

    /*
    ----------------
    | Insert Tests |
    ----------------
    */

    public function testIfAGameGetsInserted(): void
    {
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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $insertedGame = $gameService->insert(
            $game,
            $encodedToken
        );

        $this->assertEquals(
            $game->getId()->getValue(),
            $insertedGame->getId()->getValue()
        );

        $this->assertEquals(
            $game->getName()->getValue(),
            $insertedGame->getName()->getValue()
        );

        $this->assertEquals(
            $game->getIsActive(),
            $insertedGame->getIsActive()
        );
    }

    public function testIfGameInsertionFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $gameService->insert(
            $game,
            $encodedToken
        );
    }

    public function testIfGameInsertionFailsBecauseOfDuplicatedNameOnRepository(): void
    {
        $this->expectException(DuplicatedNameException::class);

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
            Name::create("Game"),
            SectorValue::from(SectorType::Game),
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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $gameService->insert(
            $game,
            $encodedToken
        );
    }

    /*
    ----------------
    | Update Tests |
    ----------------
    */

    public function testIfAValidGameGetsUpdated(): void
    {
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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $wasUpdated = $gameService->update(
            $game,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfGameUpdateFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $gameService->update(
            $game,
            $encodedToken
        );
    }

    public function testIfGameUpdateFailsBecauseOfUnexistantGameOnRepository(): void
    {
        $this->expectException(GameNotFoundException::class);

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
            SectorValue::from(SectorType::Game),
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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $gameService->update(
            $game,
            $encodedToken
        );
    }

    public function testIfGameUpdateFailsBecauseOfDuplicatedNameOnRepository(): void
    {
        $this->expectException(DuplicatedNameException::class);

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
            Name::create("Game"),
            SectorValue::from(SectorType::Game),
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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $gameService->update(
            $game,
            $encodedToken
        );
    }

    /*
    -----------------------
    | Set Is Active Tests |
    -----------------------
    */

    public function testIfGameGetsSetToActive(): void
    {
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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $isActive = true;
        $wasUpdated = $gameService->setIsActive(
            $game->getId(),
            $isActive,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfGameGetsSetToInactive(): void
    {
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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $isActive = false;
        $wasUpdated = $gameService->setIsActive(
            $game->getId(),
            $isActive,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfGameActivationFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $isActive = true;
        $gameService->setIsActive(
            $game->getId(),
            $isActive,
            $encodedToken
        );
    }

    public function testIfGameActivationFailsBecauseOfUnexistantGameOnRepository(): void
    {
        $this->expectException(GameNotFoundException::class);

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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $isActive = true;
        $gameService->setIsActive(
            $game->getId(),
            $isActive,
            $encodedToken
        );
    }

    /*
    --------------------
    | Find By Id Tests |
    --------------------
    */

    public function testIfGameGetsFoundById(): void
    {
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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $foundGame = $gameService->findById(
            $game->getId(),
            $encodedToken
        );

        $this->assertEquals(
            $game->getId(),
            $foundGame->getId()
        );

        $this->assertEquals(
            $game->getName(),
            $foundGame->getName()
        );

        $this->assertEquals(
            $game->getIsActive(),
            $foundGame->getIsActive()
        );
    }

    public function testIfGameFindByIdFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $gameService->findById(
            $game->getId(),
            $encodedToken
        );
    }

    /*
    ------------------
    | Find All Tests |
    ------------------
    */

    public function testIfAllGamesGetsFound(): void
    {
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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $games = $gameService->findAll(
            $encodedToken
        );

        $this->assertCount(
            1,
            $games->fetchAll()
        );
    }

    public function testIfAllGamesFindFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

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
        $gameDomainService = $this->createGameDomainService(
            $gameRepository
        );
        $gameService = $this->createGameService(
            $gameRepository,
            $checkAuthorizationUseCase,
            $gameDomainService
        );

        $gameService->findAll(
            $encodedToken
        );
    }
}
