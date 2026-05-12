<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\User\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\GameGenre\Service\GameGenreService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Collection\GameCollection;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\Game\Exception\GameNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Game\Repository\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Game\Service\GameDomainService;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\Collection\GameGenreCollection;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\GameGenre;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Exception\GameGenreNotFoundException;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Repository\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Service\GameGenreDomainService;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Genre\Exception\GenreNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Genre\Repository\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Genre\Service\GenreDomainService;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
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

class GameGenreServiceTest extends TestCase
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

    private function createGameGenre(
        Id $id,
        Id $gameId,
        Id $genreId,
    ): GameGenre {
        return GameGenre::create(
            $id,
            Game::createFromIdOnly(
                $gameId
            ),
            Genre::createFromIdOnly(
                $genreId
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

    private function createGenreRepository(
        bool $exists
    ): MockObject&GenreRepositoryInterface {
        $repository = $this->createMock(GenreRepositoryInterface::class);
        $repository
            ->method("checkIfExists")
            ->willReturn($exists);

        return $repository;
    }

    private function createGameGenreRepository(
        bool $exists,
        bool $isDeleting,
        GameGenre $gameGenre
    ): MockObject&GameGenreRepositoryInterface {
        $repository = $this->createMock(GameGenreRepositoryInterface::class);
        $repository
            ->method("insert")
            ->willReturn($gameGenre);
        $repository
            ->method("update")
            ->willReturn(true);
        $repository
            ->method("delete")
            ->willReturn($isDeleting);
        $repository
            ->method("findById")
            ->willReturn($gameGenre);
        $repository
            ->method("findAll")
            ->willReturn(
                new GameGenreCollection([
                    $gameGenre
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

    private function createGenreDomainService(
        MockObject&GenreRepositoryInterface $genreRepository
    ): GenreDomainService {
        $service = new GenreDomainService(
            $genreRepository
        );
        return $service;
    }

    private function createGameGenreDomainService(
        MockObject&GameGenreRepositoryInterface $gameGenreRepository
    ): GameGenreDomainService {
        $service = new GameGenreDomainService(
            $gameGenreRepository
        );
        return $service;
    }

    private function createGameGenreService(
        MockObject&GameGenreRepositoryInterface $gameGenreRepository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GameDomainService $gameDomainService,
        GenreDomainService $genreDomainService,
        GameGenreDomainService $gameGenreDomainService
    ): GameGenreService {
        $service = new GameGenreService(
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService,
            $gameGenreRepository
        );
        return $service;
    }

    /*
    ----------------
    | Insert Tests |
    ----------------
    */

    public function testIfAGameGenreGetsInserted(): void
    {
        $encodedToken = "potato";
        $gameGenre = $this->createGameGenre(
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
            Name::create("GameGenre"),
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            true,
            false,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $insertedGameGenre = $gameGenreService->insert(
            $gameGenre,
            $encodedToken
        );

        $this->assertEquals(
            $gameGenre->getId()->getValue(),
            $insertedGameGenre->getId()->getValue()
        );

        $this->assertEquals(
            $gameGenre->getGame()->getId()->getValue(),
            $insertedGameGenre->getGame()->getId()->getValue()
        );

        $this->assertEquals(
            $gameGenre->getGenre()->getId()->getValue(),
            $insertedGameGenre->getGenre()->getId()->getValue()
        );
    }

    public function testIfGameGenreInsertionFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $gameGenre = $this->createGameGenre(
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            true,
            false,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->insert(
            $gameGenre,
            $encodedToken
        );
    }

    public function testIfGameGenreInsertionFailsBecauseOfUnexistantGame(): void
    {
        $this->expectException(GameNotFoundException::class);

        $gameGenre = $this->createGameGenre(
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
            Name::create("GameGenre"),
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            true,
            false,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->insert(
            $gameGenre,
            $encodedToken
        );
    }

    public function testIfGameGenreInsertionFailsBecauseOfUnexistantGenre(): void
    {
        $this->expectException(GenreNotFoundException::class);

        $gameGenre = $this->createGameGenre(
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
            Name::create("GameGenre"),
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            false
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            true,
            false,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->insert(
            $gameGenre,
            $encodedToken
        );
    }

    /*
    ----------------
    | Update Tests |
    ----------------
    */

    public function testIfAValidGameGenreGetsUpdated(): void
    {
        $gameGenre = $this->createGameGenre(
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
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            true,
            false,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $wasUpdated = $gameGenreService->update(
            $gameGenre,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfGameGenreUpdateFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $gameGenre = $this->createGameGenre(
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            true,
            false,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->update(
            $gameGenre,
            $encodedToken
        );
    }

    public function testIfGameGenreUpdateFailsBecauseOfUnexistantGameOnRepository(): void
    {
        $this->expectException(GameNotFoundException::class);

        $gameGenre = $this->createGameGenre(
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
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            true,
            false,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->update(
            $gameGenre,
            $encodedToken
        );
    }

    public function testIfGameGenreUpdateFailsBecauseOfUnexistantGenreOnRepository(): void
    {
        $this->expectException(GenreNotFoundException::class);

        $gameGenre = $this->createGameGenre(
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
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            false
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            true,
            false,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->update(
            $gameGenre,
            $encodedToken
        );
    }

    public function testIfGameGenreUpdateFailsBecauseOfUnexistantGameGenreOnRepository(): void
    {
        $this->expectException(GameGenreNotFoundException::class);

        $gameGenre = $this->createGameGenre(
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
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            false,
            false,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->update(
            $gameGenre,
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
        $gameGenre = $this->createGameGenre(
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
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            true,
            true,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $wasDeleted = $gameGenreService->delete(
            $gameGenre->getId(),
            $encodedToken
        );

        $this->assertTrue(
            $wasDeleted
        );
    }

    public function testIfGameGenreDeletionFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $gameGenre = $this->createGameGenre(
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            false,
            true,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->delete(
            $gameGenre->getId(),
            $encodedToken
        );
    }

    public function testIfGameGenreDeletionFailsBecauseOfUnexistantGameGenreOnRepository(): void
    {
        $this->expectException(GameGenreNotFoundException::class);

        $gameGenre = $this->createGameGenre(
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
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            false,
            true,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->delete(
            $gameGenre->getId(),
            $encodedToken
        );
    }

    /*
    --------------------
    | Find By Id Tests |
    --------------------
    */

    public function testIfGameGenreGetsFoundById(): void
    {
        $gameGenre = $this->createGameGenre(
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
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            false,
            true,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $foundGameGenre = $gameGenreService->findById(
            $gameGenre->getId(),
            $encodedToken
        );

        $this->assertEquals(
            $gameGenre->getId(),
            $foundGameGenre->getId()
        );

        $this->assertEquals(
            $gameGenre->getGame()->getId(),
            $foundGameGenre->getGame()->getId()
        );

        $this->assertEquals(
            $gameGenre->getGenre()->getId(),
            $foundGameGenre->getGenre()->getId()
        );
    }

    public function testIfGameGenreFindByIdFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $gameGenre = $this->createGameGenre(
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            false,
            true,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->findById(
            $gameGenre->getId(),
            $encodedToken
        );
    }

    /*
    ------------------
    | Find All Tests |
    ------------------
    */

    public function testIfAllGameGenresGetsFound(): void
    {
        $gameGenre = $this->createGameGenre(
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
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            false,
            true,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenres = $gameGenreService->findAll(
            $encodedToken
        );

        $this->assertCount(
            1,
            $gameGenres->fetchAll()
        );
    }

    public function testIfAllGamesFindFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $gameGenre = $this->createGameGenre(
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
            SectorValue::from(SectorType::GameGenre),
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
        $genreRepository = $this->createGenreRepository(
            true
        );
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $gameGenreRepository = $this->createGameGenreRepository(
            false,
            true,
            $gameGenre
        );
        $gameGenreDomainService = $this->createGameGenreDomainService(
            $gameGenreRepository
        );
        $gameGenreService = $this->createGameGenreService(
            $gameGenreRepository,
            $checkAuthorizationUseCase,
            $gameDomainService,
            $genreDomainService,
            $gameGenreDomainService
        );

        $gameGenreService->findAll(
            $encodedToken
        );
    }
}
