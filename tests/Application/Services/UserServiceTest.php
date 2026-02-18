<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\User\UserService;
use Mvreisg\GamebaseBackend\Domain\Data\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Data\Exceptions\DataException;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\User;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserRepository;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private EncryptionInterface $encrypter;

    protected function setUp(): void
    {
        $userRepository = new MockUserRepository();
        $this->encrypter = new DefuseEncryption();
        $this->userService = new UserService(
            $userRepository,
            $this->encrypter
        );
    }

    public function testIfAInsertionAttemptOfAnActiveUserSucceds(): void
    {
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;

        $user = new User(
            $username,
            $password,
            $isActive
        );

        $insertedUser = $this->userService->insert(
            $user
        );

        $decodedPassword = $this->encrypter->decrypt($insertedUser->getPasswordValue());

        $this->assertEquals($username->getValue(), $insertedUser->getUsernameValue());
        $this->assertEquals($password->getValue(), $decodedPassword);
        $this->assertEquals($isActive, $insertedUser->getIsActive());
    }

    public function testIfAInsertionAttemptOfAnInactiveUserSucceds(): void
    {
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = false;

        $user = new User(
            $username,
            $password,
            $isActive
        );

        $insertedUser = $this->userService->insert(
            $user
        );

        $decodedPassword = $this->encrypter->decrypt($insertedUser->getPasswordValue());

        $this->assertEquals($username->getValue(), $insertedUser->getUsernameValue());
        $this->assertEquals($password->getValue(), $decodedPassword);
        $this->assertEquals($isActive, $insertedUser->getIsActive());
    }

    public function testIfAInsertionAttemptOfAActiveUserWithInvalidUsernameFails(): void
    {
        $this->expectException(DataException::class);

        $username = Username::make("-");
        $password = DecodedPassword::make("test");
        $isActive = true;

        $user = new User(
            $username,
            $password,
            $isActive
        );

        $this->userService->insert(
            $user
        );
    }

    public function testIfAInsertionAttemptOfAActiveUserWithInvalidPasswordFails(): void
    {
        $this->expectException(DataException::class);

        $username = Username::make("test");
        $password = DecodedPassword::make("-");
        $isActive = true;

        $user = new User(
            $username,
            $password,
            $isActive
        );

        $this->userService->insert(
            $user
        );
    }

    public function testIfAUpdateAttemptOfAnActiveUserSucceds(): void
    {
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = true;

        $user = new User(
            $username,
            $password,
            $isActive
        );

        $insertedUser = $this->userService->insert(
            $user
        );

        $decodedPassword = $this->encrypter->decrypt($insertedUser->getPasswordValue());

        $this->assertEquals($username->getValue(), $insertedUser->getUsernameValue());
        $this->assertEquals($password->getValue(), $decodedPassword);
        $this->assertEquals($isActive, $insertedUser->getIsActive());

        $updatedUser = new User(
            Username::make("test2"),
            DecodedPassword::make("test2"),
            $isActive
        );
        $updatedUser->setId(Id::make($insertedUser->getIdValue()));

        $wasUpdated = $this->userService->update(
            $updatedUser
        );

        $this->assertTrue($wasUpdated);
    }

    public function testIfAUpdateAttemptOfAnInactiveUserSucceds(): void
    {
        $username = Username::make("test");
        $password = DecodedPassword::make("test");
        $isActive = false;

        $user = new User(
            $username,
            $password,
            $isActive
        );

        $insertedUser = $this->userService->insert(
            $user
        );

        $decodedPassword = $this->encrypter->decrypt($insertedUser->getPasswordValue());

        $this->assertEquals($username->getValue(), $insertedUser->getUsernameValue());
        $this->assertEquals($password->getValue(), $decodedPassword);
        $this->assertEquals($isActive, $insertedUser->getIsActive());

        $updatedUser = new User(
            Username::make("test2"),
            DecodedPassword::make("test2"),
            $isActive
        );
        $updatedUser->setId(Id::make($insertedUser->getIdValue()));

        $wasUpdated = $this->userService->update(
            $updatedUser
        );

        $this->assertTrue($wasUpdated);
    }
}
