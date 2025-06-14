<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Cache\UserCacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\MockUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserRepository;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    private UserCacheInterface $userCache;
    private UserRepositoryInterface $userRepository;
    private EncryptionInterface $encrypter;
    private AuthenticationService $authService;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userCache = new MockUserCache();
        $this->userRepository = new MockUserRepository();
        $this->encrypter = new DefuseEncryption();
        $this->authService = new AuthenticationService($this->userRepository, $this->encrypter, $this->userCache);
        $this->userService = new UserService($this->userRepository, $this->encrypter);
    }

    public function testIfUserHasCredentials(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authService->tryLogin($userName, $passWord);
        $this->assertTrue($hasCredentials);
    }

    public function testIfUserDoNotHaveCredentialsWithAWrongUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authService->tryLogin('batata', $passWord);
        $this->assertFalse($hasCredentials);
    }

    public function testIfUserDoNotHaveCredentialsWithAWrongPassword(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authService->tryLogin($userName, 'batata');
        $this->assertFalse($hasCredentials);
    }

    public function testIfLoginFailsWithoutRegisteredUsers(): void
    {
        $hasCredentials = $this->authService->tryLogin('test', 'test');
        $this->assertFalse($hasCredentials);
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
            $hasCredentials = $this->authService->tryLogin($userNamePrefix . $i, $passWordPrefix . $i);
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
            $hasCredentials = $this->authService->tryLogin($userNamePrefix, $passWordPrefix);
            $this->assertFalse($hasCredentials);
        }
    }

    public function testIfLoginFailsWithEmptyUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->expectException(EntityInvalidValueException::class);
        $this->userService->insert($userName, $passWord, $isActive);
        $this->authService->tryLogin('', $passWord);
    }

    public function testIfLoginFailsWithEmptyPassWord(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->expectException(EntityInvalidValueException::class);
        $this->userService->insert($userName, $passWord, $isActive);
        $this->authService->tryLogin($userName, '');
    }

    public function testIfUserCanRetrieveAuthenticationToken(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authService->tryLogin($userName, $passWord);

        $this->assertTrue($hasCredentials);

        $oneWeek = true;
        $token = $this->authService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token, 'Token');
    }

    public function testIfGenerationOfAuthenticationTokenFailsDueToEmptyUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authService->tryLogin($userName, $passWord);

        $this->assertTrue($hasCredentials);

        $this->expectException(EntityInvalidValueException::class);

        $oneWeek = true;
        $this->authService->generateToken('', $oneWeek);
    }

    public function testIfSessionTokenIsSuccessfullyRetrievedFromCache(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authService->tryLogin($userName, $passWord);

        $this->assertTrue($hasCredentials);

        $oneWeek = true;
        $token = $this->authService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token);

        $token = $this->authService->checkIfTokenExists($userName);

        $this->assertNotEmpty($token);
    }

    public function testIfItFailsToRetrieveSessionTokenFromTheCacheDueToUnexistantUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authService->tryLogin($userName, $passWord);

        $this->assertTrue($hasCredentials);

        $oneWeek = true;
        $token = $this->authService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token);

        $token = $this->authService->checkIfTokenExists('batata');

        $this->assertEmpty($token);
    }

    public function testIfAValidTokenIsSuccessfullyValidated(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $this->userService->insert($userName, $passWord, $isActive);

        $oneWeek = true;
        $token = $this->authService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token);

        $isTokenValid = $this->authService->validateToken($token);

        $this->assertTrue($isTokenValid);
    }

    public function testIfAInvalidTokenFailsToValidate(): void
    {
        $isTokenValid = $this->authService->validateToken('');
        $this->assertFalse($isTokenValid);
    }

    public function testIfLogoffSucceds(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $hasCredentials = $this->authService->tryLogin($userName, $passWord);

        $this->assertTrue($hasCredentials);

        $oneWeek = true;
        $token = $this->authService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token);

        $hasSuccessfullyLoggedOff = $this->authService->tryLogoff($token);

        $this->assertTrue($hasSuccessfullyLoggedOff);
    }
}
