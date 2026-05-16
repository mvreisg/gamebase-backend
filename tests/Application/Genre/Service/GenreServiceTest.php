<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Genre\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Service\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Cache\AuthenticationTokenCacheInterface;
use Mvreisg\GamebaseBackend\Application\Authentication\Token\Provider\AuthenticationTokenProvider;
use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\Genre\Service\GenreService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Collection\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Genre\Exception\GenreNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Genre\Repository\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Genre\Service\GenreDomainService;
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
use Psr\Log\NullLogger;

class GenreServiceTest extends TestCase
{
    private function createGenre(
        Id $id,
        Name $name,
        bool $isActive
    ): Genre {
        return Genre::create(
            $id,
            $name,
            $isActive
        );
    }

    private function createGenreRepository(
        bool $exists,
        bool $duplicatedGameNames,
        Genre $genre
    ): MockObject&GenreRepositoryInterface {
        $repository = $this->createMock(GenreRepositoryInterface::class);
        $repository
            ->method("checkIfExists")
            ->willReturn(
                $exists
            );
        $repository
            ->method("insert")
            ->willReturn($genre);
        $repository
            ->method("update")
            ->willReturn(true);
        $repository
            ->method("setIsActive")
            ->willReturn(true);
        $repository
            ->method("findById")
            ->willReturn($genre);
        $repository
            ->method("findAll")
            ->willReturn(
                new GenreCollection([
                    $genre
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

    private function createGenreDomainService(
        MockObject&GenreRepositoryInterface $genreRepository
    ): GenreDomainService {
        $service = new GenreDomainService(
            $genreRepository
        );
        return $service;
    }

    private function createGenreService(
        MockObject&GenreRepositoryInterface $genreRepository,
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GenreDomainService $genreDomainService
    ): GenreService {
        $genreService = new GenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService,
            new NullLogger()
        );
        return $genreService;
    }

    /*
    ----------------
    | Insert Tests |
    ----------------
    */

    public function testIfAGenreGetsInserted(): void
    {
        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
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
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $insertedGenre = $genreService->insert(
            $genre,
            $encodedToken
        );

        $this->assertEquals(
            $genre->getId()->getValue(),
            $insertedGenre->getId()->getValue()
        );

        $this->assertEquals(
            $genre->getName()->getValue(),
            $insertedGenre->getName()->getValue()
        );

        $this->assertEquals(
            $genre->getIsActive(),
            $insertedGenre->getIsActive()
        );
    }

    public function testIfGenreInsertionFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $genreService->insert(
            $genre,
            $encodedToken
        );
    }

    public function testIfGenreInsertionFailsBecauseOfDuplicatedNameOnRepository(): void
    {
        $this->expectException(DuplicatedNameException::class);

        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            true,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $genreService->insert(
            $genre,
            $encodedToken
        );
    }

    /*
    ----------------
    | Update Tests |
    ----------------
    */

    public function testIfAValidGenreGetsUpdated(): void
    {
        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $wasUpdated = $genreService->update(
            $genre,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfGenreUpdateFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $genreService->update(
            $genre,
            $encodedToken
        );
    }

    public function testIfGenreUpdateFailsBecauseOfUnexistantGenreOnRepository(): void
    {
        $this->expectException(GenreNotFoundException::class);

        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            false,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $genreService->update(
            $genre,
            $encodedToken
        );
    }

    public function testIfGenreUpdateFailsBecauseOfDuplicatedNameOnRepository(): void
    {
        $this->expectException(DuplicatedNameException::class);

        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            true,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $genreService->update(
            $genre,
            $encodedToken
        );
    }

    /*
    -----------------------
    | Set Is Active Tests |
    -----------------------
    */

    public function testIfGenreGetsSetToActive(): void
    {
        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            false
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $isActive = true;
        $wasUpdated = $genreService->setIsActive(
            $genre->getId(),
            $isActive,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfGenreGetsSetToInactive(): void
    {
        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $isActive = false;
        $wasUpdated = $genreService->setIsActive(
            $genre->getId(),
            $isActive,
            $encodedToken
        );

        $this->assertTrue(
            $wasUpdated
        );
    }

    public function testIfGenreActivationFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            false
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $isActive = true;
        $genreService->setIsActive(
            $genre->getId(),
            $isActive,
            $encodedToken
        );
    }

    public function testIfGenreActivationFailsBecauseOfUnexistantGenreOnRepository(): void
    {
        $this->expectException(GenreNotFoundException::class);

        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            false
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            false,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $isActive = true;
        $genreService->setIsActive(
            $genre->getId(),
            $isActive,
            $encodedToken
        );
    }

    /*
    --------------------
    | Find By Id Tests |
    --------------------
    */

    public function testIfGenreGetsFoundById(): void
    {
        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $foundGenre = $genreService->findById(
            $genre->getId(),
            $encodedToken
        );

        $this->assertEquals(
            $genre->getId(),
            $foundGenre->getId()
        );

        $this->assertEquals(
            $genre->getName(),
            $foundGenre->getName()
        );

        $this->assertEquals(
            $genre->getIsActive(),
            $foundGenre->getIsActive()
        );
    }

    public function testIfGenreFindByIdFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $genreService->findById(
            $genre->getId(),
            $encodedToken
        );
    }

    /*
    ------------------
    | Find All Tests |
    ------------------
    */

    public function testIfAllGenresGetsFound(): void
    {
        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $genres = $genreService->findAll(
            $encodedToken
        );

        $this->assertCount(
            1,
            $genres->fetchAll()
        );
    }

    public function testIfAllGenresFindFailsBecauseOfMissingPermissions(): void
    {
        $this->expectException(UnauthorizedException::class);

        $genre = $this->createGenre(
            Id::create(1),
            Name::create("test"),
            true
        );
        $encodedToken = "potato";
        $genreRepository = $this->createGenreRepository(
            true,
            false,
            $genre
        );
        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            DecodedPassword::create("test"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("Genre"),
            SectorValue::from(SectorType::Genre),
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
        $genreDomainService = $this->createGenreDomainService(
            $genreRepository
        );
        $genreService = $this->createGenreService(
            $genreRepository,
            $checkAuthorizationUseCase,
            $genreDomainService
        );

        $genreService->findAll(
            $encodedToken
        );
    }
}
