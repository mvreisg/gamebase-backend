<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Authentication\AuthenticationException as ApplicationAuthenticationException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Authentication\AuthenticationException as InfrastructureAuthenticationException;
use Mvreisg\GamebaseBackend\Domain\Authentication\AuthenticationInterface;
use Mvreisg\GamebaseBackend\Domain\Cache\CacheInterface;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\JwtTokenAuthentication;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\MockUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserRepository;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    private CacheInterface $userCache;
    private UserRepositoryInterface $userRepository;
    private EncryptionInterface $encrypter;
    private AuthenticationInterface $authenticator;
    private AuthenticationService $authenticationService;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userCache = new MockUserCache();
        $this->userRepository = new MockUserRepository();
        $this->encrypter = new DefuseEncryption();
        $this->authenticator = new JwtTokenAuthentication();
        $this->authenticationService = new AuthenticationService(
            $this->userRepository,
            $this->encrypter,
            $this->userCache,
            $this->authenticator
        );
        $this->userService = new UserService($this->userRepository, $this->encrypter);
    }

    public function testIfUserHasCredentials(): void
    {
        $this->expectNotToPerformAssertions();
        $username = 'test';
        $password = 'test';
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $this->authenticationService->tryLogin($username, $password);
    }

    public function testIfUserDoNotHaveCredentialsWithAWrongUserName(): void
    {
        $username = 'test';
        $password = 'test';
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);

        $this->expectException(RepositoryUnexistantRegisterException::class);

        $this->authenticationService->tryLogin('batata', $password);
    }

    public function testIfUserDoNotHaveCredentialsWithAWrongPassword(): void
    {
        $username = 'test';
        $password = 'test';
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $this->expectException(ApplicationAuthenticationException::class);
        $this->authenticationService->tryLogin($username, 'batata');
    }

    public function testIfLoginFailsWithoutRegisteredUsers(): void
    {
        $this->expectException(RepositoryException::class);
        $this->authenticationService->tryLogin('test', 'test');
    }

    public function testIfLoginSuccedsWithTenUsers(): void
    {
        $usernamePrefix = 'test';
        $passWordPrefix = 'test';
        $isActive = true;
        for ($i = 1; $i <= 10; $i++) {
            $this->userService->insert($usernamePrefix . $i, $passWordPrefix . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $this->expectNotToPerformAssertions();
            $this->authenticationService->tryLogin($usernamePrefix . $i, $passWordPrefix . $i);
        }
    }

    public function testIfLoginFailsWithTenUsersButWrongCredentials(): void
    {
        $usernamePrefix = 'test';
        $passWordPrefix = 'test';
        $isActive = true;
        for ($i = 1; $i <= 10; $i++) {
            $this->userService->insert($usernamePrefix . $i, $passWordPrefix . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $this->expectException(RepositoryException::class);
            $this->authenticationService->tryLogin($usernamePrefix, $passWordPrefix);
        }
    }

    public function testIfLoginFailsWithEmptyUserName(): void
    {
        $username = 'test';
        $password = 'test';
        $isActive = true;
        $this->expectException(EntityInvalidValueException::class);
        $this->userService->insert($username, $password, $isActive);
        $this->authenticationService->tryLogin('', $password);
    }

    public function testIfLoginFailsWithEmptyPassWord(): void
    {
        $username = 'test';
        $password = 'test';
        $isActive = true;
        $this->expectException(EntityInvalidValueException::class);
        $this->userService->insert($username, $password, $isActive);
        $this->authenticationService->tryLogin($username, '');
    }

    public function testIfUserCanRetrieveAuthenticationToken(): void
    {
        $username = 'test';
        $password = 'test';
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);

        $this->authenticationService->tryLogin($username, $password);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($username, $oneWeek);

        $this->assertNotEmpty($token, 'Token');
    }

    public function testIfGenerationOfAuthenticationTokenFailsDueToEmptyUserName(): void
    {
        $username = 'test';
        $password = 'test';
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);

        $this->authenticationService->tryLogin($username, $password);

        $this->expectException(EntityInvalidValueException::class);

        $oneWeek = true;
        $this->authenticationService->generateToken('', $oneWeek);
    }

    public function testIfSessionTokenIsSuccessfullyRetrievedFromCache(): void
    {
        $username = 'test';
        $password = 'test';
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $this->authenticationService->tryLogin($username, $password);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($username, $oneWeek);

        $this->assertNotEmpty($token);

        $token = $this->authenticationService->retrieveToken($username);

        $this->assertNotEmpty($token);
    }

    public function testIfItFailsToRetrieveSessionTokenFromTheCacheDueToUnexistantUserName(): void
    {
        $username = 'test';
        $password = 'test';
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $this->authenticationService->tryLogin($username, $password);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($username, $oneWeek);

        $this->assertNotEmpty($token);

        $this->expectException(ApplicationAuthenticationException::class);

        $this->authenticationService->retrieveToken('batata');
    }

    public function testIfAValidTokenIsSuccessfullyValidated(): void
    {
        $username = 'test';
        $password = 'test';
        $isActive = true;

        $this->userService->insert($username, $password, $isActive);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($username, $oneWeek);

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
        $username = 'test';
        $password = 'test';
        $isActive = true;
        $this->userService->insert($username, $password, $isActive);
        $this->authenticationService->tryLogin($username, $password);

        $oneWeek = true;
        $token = $this->authenticationService->generateToken($username, $oneWeek);

        $this->assertNotEmpty($token);

        $this->authenticationService->tryLogoff($token);
    }
}
