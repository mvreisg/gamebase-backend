<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Domain\Cache\UserCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\MockUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserRepository;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    private UserCacheInterface $userCache;
    private UserRepositoryInterface $userRepository;
    private EncryptionInterface $encrypter;
    private AuthenticationService $authenticationService;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userCache = new MockUserCache();
        $this->userRepository = new MockUserRepository();
        $this->encrypter = new DefuseEncryption();
        $this->authenticationService = new AuthenticationService($this->userRepository, $this->encrypter, $this->userCache);
        $this->userService = new UserService($this->userRepository, $this->encrypter);
    }

    public function testIfUserHasCredentials(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authenticationService->tryLogin($userName, $passWord);
        $this->assertTrue($hasCredentials);
    }

    public function testIfUserDoNotHaveCredentialsWithAWrongUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);

        $this->expectException(DatabaseUnexistantRegisterException::class);

        $this->authenticationService->tryLogin('batata', $passWord);
    }

    public function testIfUserDoNotHaveCredentialsWithAWrongPassword(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $this->expectException(AuthenticationException::class);
        $this->authenticationService->tryLogin($userName, 'batata');
    }

    public function testIfLoginFailsWithoutRegisteredUsers(): void
    {
        $this->expectException(DatabaseUnexistantRegisterException::class);
        $hasCredentials = $this->authenticationService->tryLogin('test', 'test');
    }

    public function testIfLoginSuccedsWithTenUsers(): void
    {
        $userNamePrefix = 'test';
        $passWordPrefix = 'test';
        $isActive = true;
        for ($i = 1; $i <= 10; $i++) {
            $this->userService->insert($userNamePrefix . $i, $passWordPrefix . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $hasCredentials = $this->authenticationService->tryLogin($userNamePrefix . $i, $passWordPrefix . $i);
            $this->assertTrue($hasCredentials);
        }
    }

    public function testIfLoginFailsWithTenUsersButWrongCredentials(): void
    {
        $userNamePrefix = 'test';
        $passWordPrefix = 'test';
        $isActive = true;
        for ($i = 1; $i <= 10; $i++) {
            $this->userService->insert($userNamePrefix . $i, $passWordPrefix . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $this->expectException(DatabaseUnexistantRegisterException::class);
            $hasCredentials = $this->authenticationService->tryLogin($userNamePrefix, $passWordPrefix);
        }
    }

    public function testIfLoginFailsWithEmptyUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->expectException(EntityInvalidValueException::class);
        $this->userService->insert($userName, $passWord, $isActive);
        $this->authenticationService->tryLogin('', $passWord);
    }

    public function testIfLoginFailsWithEmptyPassWord(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->expectException(EntityInvalidValueException::class);
        $this->userService->insert($userName, $passWord, $isActive);
        $this->authenticationService->tryLogin($userName, '');
    }

    public function testIfUserCanRetrieveAuthenticationToken(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authenticationService->tryLogin($userName, $passWord);

        $this->assertTrue($hasCredentials);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token, 'Token');
    }

    public function testIfGenerationOfAuthenticationTokenFailsDueToEmptyUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authenticationService->tryLogin($userName, $passWord);

        $this->assertTrue($hasCredentials);

        $this->expectException(EntityInvalidValueException::class);

        $oneWeek = true;
        $this->authenticationService->generateToken('', $oneWeek);
    }

    public function testIfSessionTokenIsSuccessfullyRetrievedFromCache(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authenticationService->tryLogin($userName, $passWord);

        $this->assertTrue($hasCredentials);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token);

        $token = $this->authenticationService->retrieveToken($userName);

        $this->assertNotEmpty($token);
    }

    public function testIfItFailsToRetrieveSessionTokenFromTheCacheDueToUnexistantUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authenticationService->tryLogin($userName, $passWord);

        $this->assertTrue($hasCredentials);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token);

        $this->expectException(AuthenticationException::class);

        $this->authenticationService->retrieveToken('batata');
    }

    public function testIfAValidTokenIsSuccessfullyValidated(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $this->userService->insert($userName, $passWord, $isActive);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token);

        $isTokenValid = $this->authenticationService->validateToken($token);

        $this->assertTrue($isTokenValid);
    }

    public function testIfAInvalidTokenFailsToValidate(): void
    {
        $this->expectException(AuthenticationException::class);
        $isTokenValid = $this->authenticationService->validateToken('');
    }

    public function testIfLogoffSucceds(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authenticationService->tryLogin($userName, $passWord);

        $this->assertTrue($hasCredentials);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token);

        $hasSuccessfullyLoggedOff = $this->authenticationService->tryLogoff($token);

        $this->assertTrue($hasSuccessfullyLoggedOff);
    }
}
