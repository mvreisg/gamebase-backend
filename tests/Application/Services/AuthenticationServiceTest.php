<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Enums\AuthenticationLoginExistanceStatesEnum;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceUnauthorizedException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\Exceptions\AuthenticationServiceUnexistantUserException;
use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Domain\Authentication\AuthenticationInterface;
use Mvreisg\GamebaseBackend\Domain\Authentication\DTOs\AuthenticationPayloadValueDTO;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission\Permission;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector\Sector;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermission\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\UserPermission\UserPermission;
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

    public function testIfANewLoginValidForOneDayWithAnExistantUserWithoutPermissionsSucceds(): void
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

    public function testIfANewLoginValidForOneDayWithAnExistantUserWithPermissionsToSectorsSucceds(): void
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

    public function testIfANewLoginValidForOneWeekWithAnExistantUserSucceds(): void
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

    public function testIfANewLoginValidForOneDayWithAnExistantUserButWithAInvalidUsernameFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $wrongUsername = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($wrongUsername, $password, $oneWeek);
    }

    public function testIfANewLoginValidForOneWeekWithAnExistantUserButWithAInvalidUsernameFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;
        $wrongUsername = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($wrongUsername, $password, $oneWeek);
    }

    public function testIfANewLoginValidForOneDayWithAnExistantUserButWithAInvalidPasswordFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $wrongPassword = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $wrongPassword, $oneWeek);
    }

    public function testIfANewLoginValidForOneWeekWithAnExistantUserButWithAInvalidPasswordFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;
        $wrongPassword = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $wrongPassword, $oneWeek);
    }

    public function testIfANewLoginValidForOneDayWithAnUnexistantUserFails(): void
    {
        $username = "test";
        $password = "test";
        $oneWeek = false;
        $this->expectException(AuthenticationServiceUnexistantUserException::class);
        $this->authenticationService->tryLogin($username, $password, $oneWeek);
    }

    public function testIfANewLoginValidForOneWeekWithAnUnexistantUserFails(): void
    {
        $username = "test";
        $password = "test";
        $oneWeek = true;
        $this->expectException(AuthenticationServiceUnexistantUserException::class);
        $this->authenticationService->tryLogin($username, $password, $oneWeek);
    }

    public function testIfAExistantLoginValidForOneDayWithAnExistantUserSucceds(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $existingResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::Existing, $existingResult->getState());
        $this->assertNotEmpty($existingResult->getToken());
    }

    public function testIfAExistantLoginValidForOneWeekWithAnExistantUserSucceds(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $existingResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::Existing, $existingResult->getState());
        $this->assertNotEmpty($existingResult->getToken());
    }

    public function testIfAExistantLoginValidForOneDayWithAnExistantUserButInvalidUsernameFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $wrongUsername = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($wrongUsername, $password, $oneWeek);
    }

    public function testIfAExistantLoginValidForOneWeekWithAnExistantUserButInvalidUsernameFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $wrongUsername = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($wrongUsername, $password, $oneWeek);
    }

    public function testIfAExistantLoginValidForOneDayWithAnExistantUserButInvalidPasswordFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $wrongPassword = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $wrongPassword, $oneWeek);
    }

    public function testIfAExistantLoginValidForOneWeekWithAnExistantUserButInvalidPasswordFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $wrongPassword = "-";
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $wrongPassword, $oneWeek);
    }

    public function testIfAOneDayExistantLoginDoesNotLoginAfterOneDay(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $oneDayInSeconds = 60 * 60 * 24;
        $this->cacheClock->toTheFuture($oneDayInSeconds);
        $this->authenticationClock->toTheFuture($oneDayInSeconds);
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $password, $oneWeek);
    }

    public function testIfAOneDayExistantLoginDoesNotLoginAfterOneWeek(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $oneWeekInSeconds = 60 * 60 * 24 * 7;
        $this->cacheClock->toTheFuture($oneWeekInSeconds);
        $this->authenticationClock->toTheFuture($oneWeekInSeconds);
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $password, $oneWeek);
    }

    public function testIfAOneWeekExistantLoginDoesLoginAfterOneDay(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $oneDayInSeconds = 60 * 60 * 24;
        $this->cacheClock->toTheFuture($oneDayInSeconds);
        $this->authenticationClock->toTheFuture($oneDayInSeconds);
        $existantResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::Existing, $existantResult->getState());
        $this->assertNotEmpty($existantResult->getToken());
    }

    public function testIfAOneWeekExistantLoginDoesNotLoginAfterOneWeek(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;
        $newResult = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $newResult->getState());
        $this->assertNotEmpty($newResult->getToken());
        $oneWeekInSeconds = 60 * 60 * 24 * 7;
        $this->cacheClock->toTheFuture($oneWeekInSeconds);
        $this->authenticationClock->toTheFuture($oneWeekInSeconds);
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->tryLogin($username, $password, $oneWeek);
    }

    public function testIfAEmittedTokenValidForOneDayIsStillValidInThatDay(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $token = $result->getToken();
        $this->authenticationService->validateLogin($token);
    }

    public function testIfAEmittedTokenValidForOneDayTurnsInvalidAfterOneDay(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $token = $result->getToken();
        $oneDayInSeconds = 60 * 60 * 24;
        $this->cacheClock->toTheFuture($oneDayInSeconds);
        $this->authenticationClock->toTheFuture($oneDayInSeconds);
        $this->expectException(AuthenticationServiceUnauthorizedException::class);
        $this->authenticationService->validateLogin($token);
    }

    public function testIfAEmittedTokenValidForOneWeekIsStillValidInThatDay(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;
        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $token = $result->getToken();
        $this->authenticationService->validateLogin($token);
    }

    public function testIfAEmittedTokenValidForOneWeekIsStillValidAfterOneDay(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;
        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
        $token = $result->getToken();
        $oneDayInSeconds = 60 * 60 * 24;
        $this->cacheClock->toTheFuture($oneDayInSeconds);
        $this->authenticationClock->toTheFuture($oneDayInSeconds);
        $this->authenticationService->validateLogin($token);
    }

    public function testIfAEmittedTokenValidForOneWeekTurnsInvalidAfterOneWeek(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = true;
        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
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

    public function testIfALogoffSuccedsWithAValidToken(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $oneWeek = false;
        $result = $this->authenticationService->tryLogin($username, $password, $oneWeek);
        $this->assertEquals(AuthenticationLoginExistanceStatesEnum::New, $result->getState());
        $this->assertNotEmpty($result->getToken());
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
