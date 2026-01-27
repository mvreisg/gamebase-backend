<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Enums\AuthenticationLoginExistanceStatesEnum;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceUnauthorizedException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceUnexistantUserException;
use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Domain\Authentication\AuthenticationInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\DTOs\AuthenticationPayloadValueDTO;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Data\Root\Permission\Permission;
use Mvreisg\GamebaseBackend\Domain\Data\Root\Sector\Sector;
use Mvreisg\GamebaseBackend\Domain\Data\Root\SectorPermission\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Data\Root\UserPermission\UserPermission;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Entities\MockTokenAuthenticationClock;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\MockTokenAuthentication;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\Entities\MockCacheClock;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\MockUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockSectorPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserRepository;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    private MockCacheClock $cacheClock;
    private CacheInterface $userCache;
    private UserRepositoryInterface $userRepository;
    private PermissionRepositoryInterface $permissionRepository;
    private SectorRepositoryInterface $sectorRepository;
    private UserPermissionRepositoryInterface $userPermissionRepository;
    private SectorPermissionRepositoryInterface $sectorPermissionRepository;
    private EncryptionInterface $encrypter;
    private MockTokenAuthenticationClock $authenticationClock;
    private AuthenticationInterface $authenticator;
    private AuthenticationService $authenticationService;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->cacheClock = new MockCacheClock(
            new \DateTimeImmutable()
        );
        $this->userCache = new MockUserCache(
            $this->cacheClock
        );
        $this->userRepository = new MockUserRepository();
        $this->permissionRepository = new MockPermissionRepository();
        $this->sectorRepository = new MockSectorRepository();
        $this->userPermissionRepository = new MockUserPermissionRepository();
        $this->sectorPermissionRepository = new MockSectorPermissionRepository();
        $this->encrypter = new DefuseEncryption();
        $this->authenticationClock = new MockTokenAuthenticationClock(
            new \DateTimeImmutable()
        );
        $this->authenticator = new MockTokenAuthentication(
            $this->authenticationClock
        );
        $this->authenticationService = new AuthenticationService(
            $this->userRepository,
            $this->permissionRepository,
            $this->sectorRepository,
            $this->userPermissionRepository,
            $this->sectorPermissionRepository,
            $this->encrypter,
            $this->userCache,
            $this->authenticator,
            $this->authenticationClock
        );
        $this->userService = new UserService($this->userRepository, $this->encrypter);
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithoutPermissionsSucceds(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $insertedUser = $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);

        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $this->assertEquals(
            new AuthenticationPayloadValueDTO(
                $insertedUser->getId(),
                $insertedUser->getUsername()
            ),
            $result->getDto()
        );
    }

    public function testIfANewLoginValidForOneDayWithARegisteredUserWithPermissionsToSectorsSucceds(): void
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
    }

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
}
