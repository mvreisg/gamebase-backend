<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceInvalidCredentialsException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginInfo;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginStates;
use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\Exceptions\AuthenticationTokenDecoderException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Cache\Token\Exceptions\TokenCacheException;
use Mvreisg\GamebaseBackend\Domain\Data\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Data\Exceptions\DataException;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Permission;
use Mvreisg\GamebaseBackend\Domain\Data\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Sector;
use Mvreisg\GamebaseBackend\Domain\Data\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\User;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Clock\MockAuthenticationTokenClock;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Decoder\MockAuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Encoder\MockAuthenticationTokenEncoder;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Validator\Decoded\MockDecodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Validator\Encoded\MockEncodedAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Token\MockTokenCache;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Token\Clock\MockTokenCacheClock;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserRepository;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    private UserService $userService;
    private PermissionRepositoryInterface $permissionRepository;
    private SectorRepositoryInterface $sectorRepository;
    private UserPermissionRepositoryInterface $userPermissionRepository;
    private SectorPermissionRepositoryInterface $sectorPermissionRepository;
    private EncryptionInterface $encrypter;
    private AuthenticationService $authenticationService;
    private MockTokenCacheClock $tokenCacheClock;
    private MockAuthenticationTokenClock $authenticationTokenClock;

    protected function setUp(): void
    {
        $now = new \DateTimeImmutable();
        $userRepository = new MockUserRepository();
        $this->tokenCacheClock = new MockTokenCacheClock(
            $now
        );
        $tokenCache = new MockTokenCache(
            $this->tokenCacheClock
        );
        $this->encrypter = new DefuseEncryption();
        $this->userService = new UserService(
            $userRepository,
            $this->encrypter
        );
        $this->authenticationTokenClock = new MockAuthenticationTokenClock(
            $now
        );
        $authenticationTokenEncoder = new MockAuthenticationTokenEncoder(
            $this->authenticationTokenClock
        );
        $authenticationTokenDecoder = new MockAuthenticationTokenDecoder(
            $this->authenticationTokenClock
        );
        $this->permissionRepository = new MockPermissionRepository();
        $this->sectorRepository = new MockSectorRepository();
        $this->sectorPermissionRepository = new MockSectorPermissionRepository();
        $this->userPermissionRepository = new MockUserPermissionRepository();
        $encodedAuthenticationTokenValidator = new MockEncodedAuthenticationTokenValidator(
            $authenticationTokenDecoder,
            new MockDecodedAuthenticationTokenValidator(
                $this->authenticationTokenClock
            )
        );

        $this->authenticationService = new AuthenticationService(
            $userRepository,
            $tokenCache,
            $this->encrypter,
            $authenticationTokenEncoder,
            $authenticationTokenDecoder,
            $this->permissionRepository,
            $this->sectorRepository,
            $this->sectorPermissionRepository,
            $this->userPermissionRepository,
            $encodedAuthenticationTokenValidator
        );
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithoutPermissionsSucceds(): void
    {
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->assertSame(
            $username->getValue(),
            $insertedUser->getUsernameValue()
        );
        $this->assertSame(
            $password->getValue(),
            $this->encrypter->decrypt(
                $insertedUser->getPasswordValue()
            )
        );
        $this->assertSame(
            $isActive,
            $insertedUser->getIsActive()
        );
        $this->expectException(AuthenticationServiceInvalidCredentialsException::class);

        $oneWeekLogin = false;
        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                DecodedPassword::make("a"),
                $oneWeekLogin
            )
        );
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsSucceds(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                Name::make("permission"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                Name::make("sector"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $result = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $result->getState());
        $this->assertNotEmpty($result->getToken());

        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $result->getData()
        );
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithoutPermissionsSucceds(): void
    {
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $oneWeekLogin = true;
        $result = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $result->getState());
        $this->assertNotEmpty($result->getToken());

        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection(null),
                new SectorCollection(null),
                new SectorPermissionCollection(null)
            ),
            $result->getData()
        );
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithPermissionToSectorsSucceds(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $result = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $result->getState());
        $this->assertNotEmpty($result->getToken());

        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $result->getData()
        );
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithAInvalidUsernameFails(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $this->expectException(DataException::class);

        $wrongUsername = Username::make("-");
        $oneWeekLogin = true;

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $wrongUsername,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidUsernameFails(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;

        $this->expectException(DataException::class);
        $wrongUsername = Username::make("-");

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $wrongUsername,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;

        $this->expectException(DataException::class);
        $username = Username::make("test");
        $wrongPassword = DecodedPassword::make("-");

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $wrongPassword,
                $oneWeekLogin
            )
        );
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;

        $this->expectException(DataException::class);
        $username = Username::make("test");
        $wrongPassword = DecodedPassword::make("-");

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $wrongPassword,
                $oneWeekLogin
            )
        );
    }

    public function testIfANewLoginValidForOneDayWithInvalidCredentialsFails(): void
    {
        $username = Username::make("test");
        $password = DecodedPassword::make("test");

        $oneWeekLogin = false;
        $this->expectException(RepositoryUnexistantRegisterException::class);
        $this->authenticationService->tryLogin(new AuthenticationLoginInfo(
            $username,
            $password,
            $oneWeekLogin
        ));
    }

    public function testIfANewLoginValidForOneWeekWithInvalidCredentialsFails(): void
    {
        $username = Username::make("test");
        $password = DecodedPassword::make("test");

        $oneWeekLogin = true;
        $this->expectException(RepositoryUnexistantRegisterException::class);
        $this->authenticationService->tryLogin(new AuthenticationLoginInfo(
            $username,
            $password,
            $oneWeekLogin
        ));
    }

    public function testIfAExistantLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsSucceds(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $existingResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::Existing, $existingResult->getState());
        $this->assertNotEmpty($existingResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $existingResult->getData()
        );
    }

    public function testIfAExistantLoginValidForOneWeekWithARegisteredUserWithPermissionToSectorsSucceds(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $existingResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::Existing, $existingResult->getState());
        $this->assertNotEmpty($existingResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $existingResult->getData()
        );
    }

    public function testIfAExistantLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithInvalidUsernameFails(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $this->expectException(DataException::class);
        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                Username::make("-"),
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAExistantLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidUsernameFails(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $this->expectException(DataException::class);
        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                Username::make("-"),
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAExistantLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $this->expectException(DataException::class);
        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                DecodedPassword::make("-"),
                $oneWeekLogin
            )
        );
    }

    public function testIfAExistantLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $this->expectException(DataException::class);
        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                DecodedPassword::make("-"),
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneDayBecauseOfExpiredTokenOnCache(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");

        $this->expectException(TokenCacheException::class);

        $this->tokenCacheClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneDayBecauseOfExpiredTokenWhenValidating(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");

        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationTokenClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneWeekBecauseOfExpiredTokenOnCache(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->expectException(TokenCacheException::class);

        $this->tokenCacheClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneWeekBecauseOfExpiredTokenWhenValidating(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationTokenClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneWeekExistantLoginByAUserWithPermissionsToSectorsDoesLoginAfterOneDay(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");

        $this->tokenCacheClock->advance(
            $interval
        );
        $this->authenticationTokenClock->advance(
            $interval
        );

        $existingResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::Existing, $existingResult->getState());
        $this->assertNotEmpty($existingResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $existingResult->getData()
        );
    }

    public function testIfAOneWeekExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneWeekBecauseOfExpiredTokenOnCache(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->expectException(TokenCacheException::class);

        $this->tokenCacheClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAOneWeekExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneWeekBecauseOfExpiredTokenWhenValidating(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationTokenClock->advance(
            $interval
        );

        $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );
    }

    public function testIfAEmittedTokenValidForOneDayToAUserWithPermissionsToSectorsIsStillValidInThatDay(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $validationResult = $this->authenticationService->validateToken(
            $newResult->getToken()
        );

        $this->assertEquals(
            $newResult->getToken()->getToken(),
            $validationResult->getToken()->getToken()
        );
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            new AuthenticationData(
                $validationResult->getUserId(),
                $validationResult->getUsername(),
                $validationResult->getPermissionCollection(),
                $validationResult->getSectorCollection(),
                $validationResult->getSectorPermissionCollection()
            )
        );
    }

    public function testIfAEmittedTokenValidForOneDayToAUserWithPermissionsToSectorsTurnsInvalidAfterOneDayBecauseOfExpiredTokenOnCache(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");
        $this->tokenCacheClock->advance(
            $interval
        );

        $this->expectException(TokenCacheException::class);

        $this->authenticationService->validateToken(
            $newResult->getToken()
        );
    }

    public function testIfAEmittedTokenValidForOneDayToAUserWithPermissionsToSectorsTurnsInvalidAfterOneDayBecauseOfExpiredTokenWhenValidating(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = false;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");
        $this->authenticationTokenClock->advance(
            $interval
        );

        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationService->validateToken(
            $newResult->getToken()
        );
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsIsStillValidInThatDay(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");
        $this->tokenCacheClock->advance(
            $interval
        );
        $this->authenticationTokenClock->advance(
            $interval
        );

        $validationResult = $this->authenticationService->validateToken(
            $newResult->getToken()
        );

        $this->assertEquals(
            $newResult->getToken()->getToken(),
            $validationResult->getToken()->getToken()
        );
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            new AuthenticationData(
                $validationResult->getUserId(),
                $validationResult->getUsername(),
                $validationResult->getPermissionCollection(),
                $validationResult->getSectorCollection(),
                $validationResult->getSectorPermissionCollection()
            )
        );
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsIsStillValidAfterOneDay(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P1D");
        $this->tokenCacheClock->advance(
            $interval
        );
        $this->authenticationTokenClock->advance(
            $interval
        );

        $validationResult = $this->authenticationService->validateToken(
            $newResult->getToken()
        );

        $this->assertEquals(
            $newResult->getToken()->getToken(),
            $validationResult->getToken()->getToken()
        );
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            new AuthenticationData(
                $validationResult->getUserId(),
                $validationResult->getUsername(),
                $validationResult->getPermissionCollection(),
                $validationResult->getSectorCollection(),
                $validationResult->getSectorPermissionCollection()
            )
        );
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsTurnsInvalidAfterOneWeekBecauseOfExpiredTokenOnCache(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->tokenCacheClock->advance(
            $interval
        );

        $this->expectException(TokenCacheException::class);

        $this->authenticationService->validateToken(
            $newResult->getToken()
        );
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsTurnsInvalidAfterOneWeekBecauseOfExpiredTokenWhenValidating(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $insertedSectorPermission = $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $newResult = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->assertEquals(AuthenticationLoginStates::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationData(
                Id::make($insertedUser->getIdValue()),
                Username::make($insertedUser->getUsernameValue()),
                new PermissionCollection([
                    $insertedPermission
                ]),
                new SectorCollection([
                    $insertedSector
                ]),
                new SectorPermissionCollection([
                    $insertedSectorPermission
                ])
            ),
            $newResult->getData()
        );

        $interval = new \DateInterval("P7D");

        $this->authenticationTokenClock->advance(
            $interval
        );

        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationService->validateToken(
            $newResult->getToken()
        );
    }

    public function testIfAInvalidTokenDoesNotValidate(): void
    {
        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationService->validateToken(
            new EncodedAuthenticationToken(
                "batata"
            )
        );
    }

    public function testIfALogoffSuccedsWithAValidTokenInformedByAUserWithPermissionsToSectors(): void
    {
        $permission = new Permission(
            Name::make("permission"),
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            Name::make("sector"),
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                $username,
                $password,
                $isActive
            )
        );

        $this->userPermissionRepository->insert(
            new UserPermission(
                Id::make($insertedUser->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                Id::make($insertedSector->getIdValue()),
                Id::make($insertedPermission->getIdValue())
            )
        );

        $oneWeekLogin = true;
        $result = $this->authenticationService->tryLogin(
            new AuthenticationLoginInfo(
                $username,
                $password,
                $oneWeekLogin
            )
        );

        $this->expectNotToPerformAssertions();

        $this->authenticationService->tryLogoff(
            $result->getToken()
        );
    }

    public function testIfAInvalidTokenDoesNotLogoff(): void
    {
        $this->expectException(AuthenticationTokenDecoderException::class);

        $this->authenticationService->tryLogoff(
            new EncodedAuthenticationToken(
                "batata"
            )
        );
    }
}
