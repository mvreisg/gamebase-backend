<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Cache\Mock\MockUserCache;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserRepository;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    //
    // Login
    //

    public function testIfLoginSuccedsWithARegisteredUser()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertTrue($authService->login('test', 'test'));
    }

    public function testIfLoginFailsWithARegisteredUserAndWrongUsername()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertFalse($authService->login('test2', 'test'));
    }

    public function testIfLoginFailsWithARegisteredUserAndWrongPassword()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertFalse($authService->login('test', 'test2'));
    }

    public function testIfLoginFailsWithoutRegisteredUsers()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);

        $this->assertFalse($authService->login('test', 'test'));
    }

    public function testIfLoginSuccedsWithTenUsers()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        for ($i = 1; $i <= 10; $i++) {
            $userService->insert('test' . $i, 'test' . $i, true);
        }
        $index = random_int(1, 10);
        $this->assertTrue($authService->login('test' . $index, 'test' . $index));
    }

    public function testIfLoginFailsWithTenUsersButWrongCredentials()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        for ($i = 1; $i <= 10; $i++) {
            $userService->insert('test' . $i, 'test' . $i, true);
        }
        $this->assertFalse($authService->login('test11', 'test11'));
    }

    public function testIfLoginFailsWithCorrectUserNameButWrongPassWord()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertFalse($authService->login('test', 'test2'));
    }

    public function testIfLoginFailsWithWrongUserNameButCorrectPassWord()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertFalse($authService->login('test2', 'test'));
    }

    public function testIfLoginFailsWithEmptyUserName()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login('', 'test');
    }

    public function testIfLoginFailsWithBooleanUserName()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login(true, 'test');
    }

    public function testIfLoginFailsWithNumberUserName()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login(1, 'test');
    }

    public function testIfLoginFailsWithArrayUserName()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login([], 'test');
    }

    public function testIfLoginFailsWithEmptyPassWord()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', '');
    }

    public function testIfLoginFailsWithNullPassWord()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', null);
    }

    public function testIfLoginFailsWithNumberPassWord()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', 1);
    }

    public function testIfLoginFailsWithArrayPassWord()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', []);
    }

    //
    // Generate Token
    //

    public function testIfAuthenticationTokenForOneDayIsSuccessfullyGenerated()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $this->assertIsString($authService->generateToken('test', false));
    }

    public function testIfAuthenticationTokenForOneWeekIsSuccessfullyGenerated()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $this->assertIsString($authService->generateToken('test', true));
    }

    public function testIfGenerationOfAuthenticationTokenForOneDayFailsDueToEmptyUserName()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $authService->generateToken('', true);
    }

    //
    // Set Session Token
    //

    public function testIfSessionTokenIsSuccessfullySetted()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectNotToPerformAssertions();

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
    }

    public function testIfItFailsToSetTheSessionTokenDueToInvalidToken()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(AuthenticationException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $authService->generateToken('test', true);
        $authService->setSessionToken('test', '');
    }

    //
    // Get Session Token
    //

    public function testIfSessionTokenIsSuccessfullyRetrievedFromCache()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectNotToPerformAssertions();

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $authService->getSessionToken('test');
    }

    public function testIfItFailsToRetrieveSessionTokenFromTheCacheDueToUnexistantUserName()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);

        $this->expectException(AuthenticationException::class);

        $authService->getSessionToken('test2');
    }

    public function testIfItSuccessfullyHaveSession()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $this->assertTrue($authService->getSessionToken('test'));
    }

    public function testIfItDoesNotHaveSessionEvenWithValidUsername()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(AuthenticationException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('test');
    }

    public function testIfItDoesNotHaveSessionWithAUnexistantUsername()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $this->assertFalse($authService->getSessionToken('test2'));
    }

    public function testIfItDoesNotHaveSessionWithAEmptyUsername()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('');
    }

    //
    // Validate Token
    //

    public function testIfAValidTokenIsSuccessfullyValidated()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(AuthenticationException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('test');
        $authService->validateToken('test', $token);
    }

    public function testIfAInvalidTokenFailsToValidate()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(AuthenticationException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('test');
        $authService->validateToken('batatapotato', $token);
    }

    //
    // Decode Token
    //

    public function testIfTokenSubscriptionIsValidAfterDecode()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('test');
        $authService->validateToken('test', $token);
        $sub = $authService->decodeToken($token);
        $this->assertEquals('test', $sub);
    }

    public function testIfTokenSubscriptionIsInvalidAfterDecode()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('test');
        $authService->validateToken('test', $token);
        $sub = $authService->decodeToken($token);
        $this->assertNotEquals('batatapotato', $sub);
    }

    public function testIfTokenDecodingFailsWithInvalidToken()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(AuthenticationException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('test');
        $authService->validateToken('test', $token);
        $authService->decodeToken('batatapotato');
    }

    public function testIfTokenDecodingFailsWithEmptyToken()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(AuthenticationException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('test');
        $authService->validateToken('test', $token);
        $authService->decodeToken('');
    }

    //
    // Logoff
    //

    public function testIfLogoffSucceds()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('test');
        $authService->validateToken('test', $token);
        $userName = $authService->decodeToken($token);
        $this->assertTrue($authService->logoff($userName->sub));
    }

    public function testIfLogoffFailsWithUnexistantUserName()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('test');
        $authService->validateToken('test', $token);
        $userName = $authService->decodeToken($token);
        $this->assertFalse($authService->logoff($userName->sub));
    }

    public function testIfLogoffFailsWithEmptyUserName()
    {
        $userCache = new MockUserCache();
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $authService = new AuthenticationService($userRepository, $encrypter, $userCache);
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $authService->login('test', 'test');
        $token = $authService->generateToken('test', true);
        $authService->setSessionToken('test', $token);
        $token = $authService->getSessionToken('test');
        $authService->getSessionToken('test');
        $authService->validateToken('test', $token);
        $userName = $authService->decodeToken($token);
        $authService->logoff('');
    }
}
