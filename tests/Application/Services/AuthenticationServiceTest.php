<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceInvalidCredentialsException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginInfo;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Login\AuthenticationLoginStates;
use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Data\AuthenticationData;
use Mvreisg\GamebaseBackend\Domain\Data\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Permission;
use Mvreisg\GamebaseBackend\Domain\Data\PermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Sector;
use Mvreisg\GamebaseBackend\Domain\Data\SectorCollection;
use Mvreisg\GamebaseBackend\Domain\Data\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\User;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
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

    protected function setUp(): void
    {
        $now = new \DateTimeImmutable();
        $userRepository = new MockUserRepository();
        $tokenCacheClock = new MockTokenCacheClock(
            $now
        );
        $tokenCache = new MockTokenCache(
            $tokenCacheClock
        );
        $this->encrypter = new DefuseEncryption();
        $this->userService = new UserService(
            $userRepository,
            $this->encrypter
        );
        $authenticationTokenClock = new MockAuthenticationTokenClock(
            $now
        );
        $authenticationTokenEncoder = new MockAuthenticationTokenEncoder(
            $authenticationTokenClock
        );
        $authenticationTokenDecoder = new MockAuthenticationTokenDecoder(
            $authenticationTokenClock
        );
        $this->permissionRepository = new MockPermissionRepository();
        $this->sectorRepository = new MockSectorRepository();
        $this->sectorPermissionRepository = new MockSectorPermissionRepository();
        $this->userPermissionRepository = new MockUserPermissionRepository();
        $encodedAuthenticationTokenValidator = new MockEncodedAuthenticationTokenValidator(
            $authenticationTokenDecoder,
            new MockDecodedAuthenticationTokenValidator(
                $authenticationTokenClock
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

    /*
    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsSucceds(): void
    {
        $insertedPermission = $this->permissionRepository->insert(
            new Permission(
                null,
                Name::make("permission"),
                true
            )
        );

        $insertedSector = $this->sectorRepository->insert(
            new Sector(
                null,
                Name::make("sector"),
                true
            )
        );

        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;
        $insertedUser = $this->userService->insert(
            new User(
                null,
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
                ])
            ),
            $result->getData()
        );
    }
    */

    /*
    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithoutPermissionsSucceds(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;

        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());

        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
            ),
            $result->getDto()
        );
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithPermissionToSectorsSucceds(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;
        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());

        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $result->getDto()
        );
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithAInvalidUsernameFails(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = false;
        $wrongUsername = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($wrongUsername, $password, $oneWeek);
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidUsernameFails(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;
        $wrongUsername = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($wrongUsername, $password, $oneWeek);
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = false;
        $wrongPassword = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $wrongPassword, $oneWeek);
    }

    public function testIfANewLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;
        $wrongPassword = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $wrongPassword, $oneWeek);
    }

    public function testIfANewLoginValidForOneDayWithInvalidCredentialsFails(): void
    {
        $username = "test";
        $password = "test";
        $oneWeek = false;
        $this->expectException(AuthenticationServiceUnexistantUserException::class);
        $this->authenticationService->tryLogin($username, $password, $oneWeek);
    }

    public function testIfANewLoginValidForOneWeekWithInvalidCredentialsFails(): void
    {
        $username = "test";
        $password = "test";
        $oneWeek = true;
        $this->expectException(AuthenticationServiceUnexistantUserException::class);
        $this->authenticationService->tryLogin($username, $password, $oneWeek);
    }

    public function testIfAExistantLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsSucceds(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = false;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $newResult->getDto()
        );

        $existingResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::Existing, $existingResult->getState());
        $this->assertNotEmpty($existingResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $existingResult->getDto()
        );
    }

    public function testIfAExistantLoginValidForOneWeekWithARegisteredUserWithPermissionToSectorsSucceds(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;

        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $newResult->getDto()
        );

        $existingResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::Existing, $existingResult->getState());
        $this->assertNotEmpty($existingResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $existingResult->getDto()
        );
    }

    public function testIfAExistantLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithInvalidUsernameFails(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = false;

        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $newResult->getDto()
        );

        $wrongUsername = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($wrongUsername, $password, $oneWeek);
    }

    public function testIfAExistantLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidUsernameFails(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;

        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $newResult->getDto()
        );

        $wrongUsername = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($wrongUsername, $password, $oneWeek);
    }

    public function testIfAExistantLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = false;

        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $newResult->getDto()
        );

        $wrongPassword = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $wrongPassword, $oneWeek);
    }

    public function testIfAExistantLoginValidForOneWeekWithARegisteredUserWithPermissionsToSectorsButWithAInvalidPasswordFails(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;

        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $newResult->getDto()
        );

        $wrongPassword = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $wrongPassword, $oneWeek);
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneDay(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = false;

        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $newResult->getDto()
        );

        $oneDayInSeconds = 60 * 60 * 24;

        $this->cacheClock->toTheFuture($oneDayInSeconds);
        $this->authenticationClock->toTheFuture($oneDayInSeconds);
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $password, $oneWeek);
    }

    public function testIfAOneDayExistantLoginByAUserWithPermissionToSectorsDoesNotLoginAfterOneWeek(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = false;

        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $newResult->getDto()
        );

        $oneWeekInSeconds = 60 * 60 * 24 * 7;

        $this->cacheClock->toTheFuture($oneWeekInSeconds);
        $this->authenticationClock->toTheFuture($oneWeekInSeconds);
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $password, $oneWeek);
    }

    public function testIfAOneWeekExistantLoginByAUserWithPermissionsToSectorsDoesLoginAfterOneDay(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;

        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $newResult->getDto()
        );

        $oneDayInSeconds = 60 * 60 * 24;

        $this->cacheClock->toTheFuture($oneDayInSeconds);
        $this->authenticationClock->toTheFuture($oneDayInSeconds);

        $existantResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::Existing, $existantResult->getState());
        $this->assertNotEmpty($existantResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $existantResult->getDto()
        );
    }

    public function testIfAOneWeekExistantLoginByAUserWithPermissionsToSectorsDoesNotLoginAfterOneWeek(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;

        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $newResult->getDto()
        );

        $oneWeekInSeconds = 60 * 60 * 24 * 7;

        $this->cacheClock->toTheFuture($oneWeekInSeconds);
        $this->authenticationClock->toTheFuture($oneWeekInSeconds);

        $this->expectException(AuthenticationServiceUnauthorizedException::class);

        $this->authenticationService->tryLogin($username, $password, $oneWeek);
    }

    public function testIfAEmittedTokenValidForOneDayToAUserWithPermissionsToSectorsIsStillValidInThatDay(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = false;

        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $result->getDto()
        );

        $token = $result->getToken();
        $this->authenticationService->validateLogin($token);
    }

    public function testIfAEmittedTokenValidForOneDayToAUserWithPermissionsToSectorsTurnsInvalidAfterOneDay(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = false;

        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $result->getDto()
        );

        $token = $result->getToken();
        $oneDayInSeconds = 60 * 60 * 24;

        $this->cacheClock->toTheFuture($oneDayInSeconds);
        $this->authenticationClock->toTheFuture($oneDayInSeconds);

        $this->expectException(AuthenticationServiceUnauthorizedException::class);

        $this->authenticationService->validateLogin($token);
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsIsStillValidInThatDay(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;

        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $result->getDto()
        );

        $token = $result->getToken();

        $this->authenticationService->validateLogin($token);
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsIsStillValidAfterOneDay(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;

        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $result->getDto()
        );

        $token = $result->getToken();
        $oneDayInSeconds = 60 * 60 * 24;

        $this->cacheClock->toTheFuture($oneDayInSeconds);
        $this->authenticationClock->toTheFuture($oneDayInSeconds);

        $this->authenticationService->validateLogin($token);
    }

    public function testIfAEmittedTokenValidForOneWeekToAUserWithPermissionsToSectorsTurnsInvalidAfterOneWeek(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = true;

        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $result->getDto()
        );

        $token = $result->getToken();
        $oneWeekInSeconds = 60 * 60 * 24 * 7;

        $this->cacheClock->toTheFuture($oneWeekInSeconds);
        $this->authenticationClock->toTheFuture($oneWeekInSeconds);

        $this->expectException(AuthenticationServiceUnauthorizedException::class);

        $this->authenticationService->validateLogin($token);
    }

    public function testIfAInvalidTokenDoesNotValidate(): void
    {
        $token = "abcde";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->validateLogin($token);
    }

    public function testIfALogoffSuccedsWithAValidTokenInformedByAUserWithPermissionsToSectors(): void
    {
        $permission = new Permission(
            null,
            "permission 1",
            true
        );

        $insertedPermission = $this->permissionRepository->insert($permission);

        $sector = new Sector(
            null,
            "sector 1",
            true
        );

        $insertedSector = $this->sectorRepository->insert($sector);

        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);

        $this->userPermissionRepository->insert(
            new UserPermission(
                null,
                $insertedUser->getId(),
                $insertedPermission->getId()
            )
        );
        $this->sectorPermissionRepository->insert(
            new SectorPermission(
                null,
                $insertedSector->getId(),
                $insertedPermission->getId()
            )
        );

        $oneWeek = false;

        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername(),
                [
                    $insertedPermission->getId()
                ],
                [
                    $insertedSector->getId()
                ]
            ),
            $result->getDto()
        );

        $token = $result->getToken();

        $this->authenticationService->tryLogoff($token);
    }

    public function testIfAInvalidTokenDoesNotLogoff(): void
    {
        $token = "abcde";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogoff($token);
    }
    */
}
