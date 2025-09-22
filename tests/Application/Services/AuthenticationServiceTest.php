<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Authentication\AuthenticationException as ApplicationAuthenticationException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Authentication\AuthenticationException as InfrastructureAuthenticationException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\TokenAuthenticationInterface;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Exceptions\Entities\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\JwtTokenAuthentication;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\MockUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserEntityRepository;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    private CacheInterface $userCache;
    private UserEntityRepositoryInterface $userEntityRepository;
    private EncryptionInterface $encrypter;
    private TokenAuthenticationInterface $authenticator;
    private AuthenticationService $authenticationService;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userCache = new MockUserCache();
        $this->userEntityRepository = new MockUserEntityRepository();
        $this->encrypter = new DefuseEncryption();
        $this->authenticator = new JwtTokenAuthentication();
        $this->authenticationService = new AuthenticationService(
            $this->userEntityRepository,
            $this->encrypter,
            $this->userCache,
            $this->authenticator
        );
        $this->userService = new UserService($this->userEntityRepository, $this->encrypter);
    }

    public function testIfUserHasCredentials(): void
    {
        $this->expectNotToPerformAssertions();
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $this->authenticationService->tryLogin($userName, $passWord);
    }

    public function testIfUserDoNotHaveCredentialsWithAWrongUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);

        $this->expectException(RepositoryUnexistantRegisterException::class);

        $this->authenticationService->tryLogin('batata', $passWord);
    }

    public function testIfUserDoNotHaveCredentialsWithAWrongPassword(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $this->expectException(ApplicationAuthenticationException::class);
        $this->authenticationService->tryLogin($userName, 'batata');
    }

    public function testIfLoginFailsWithoutRegisteredUsers(): void
    {
        $this->expectException(RepositoryException::class);
        $this->authenticationService->tryLogin('test', 'test');
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
            $this->expectNotToPerformAssertions();
            $this->authenticationService->tryLogin($userNamePrefix . $i, $passWordPrefix . $i);
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
            $this->expectException(RepositoryException::class);
            $this->authenticationService->tryLogin($userNamePrefix, $passWordPrefix);
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

        $this->authenticationService->tryLogin($userName, $passWord);

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

        $this->authenticationService->tryLogin($userName, $passWord);

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
        $this->authenticationService->tryLogin($userName, $passWord);

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
        $this->authenticationService->tryLogin($userName, $passWord);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token);

        $this->expectException(ApplicationAuthenticationException::class);

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
        $this->expectException(InfrastructureAuthenticationException::class);
        $isTokenValid = $this->authenticationService->validateToken('');
    }

    public function testIfLogoffSucceds(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;
        $this->userService->insert($userName, $passWord, $isActive);
        $this->authenticationService->tryLogin($userName, $passWord);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($userName, $oneWeek);

        $this->assertNotEmpty($token);

        $this->authenticationService->tryLogoff($token);
    }
}
