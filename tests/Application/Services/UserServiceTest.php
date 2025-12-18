<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\User\User;
use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserRepository;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private EncryptionInterface $encrypter;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userRepository = new MockUserRepository();
        $this->encrypter = new DefuseEncryption();
        $this->userService = new UserService($this->userRepository, $this->encrypter);
    }

    public function testIfInsertSucceds(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $user = $this->userService->insert($username, $password, $isActive);

        $this->assertNotEmpty($user);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testIfInsertingUserNameThatAlreadyExistsFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $this->expectException(MockDuplicatedEntryException::class);

        $this->userService->insert($username, $password, $isActive);
        $this->userService->insert($username, $password, $isActive);
    }

    public function testIfInsertFailsWithEmptyUserName(): void
    {
        $username = "";
        $password = "test";
        $isActive = true;

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->insert($username, $password, $isActive);
    }

    public function testIfInsertFailsWithEmptyPassword(): void
    {
        $username = "test";
        $password = "";
        $isActive = true;

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->insert($username, $password, $isActive);
    }

    public function testIfUpdateSuccessfullyHappensWithOneUser(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $user = $this->userService->insert($username, $password, $isActive);

        $id = $user->getId();

        $hasChanged = $this->userService->update($id, $username, $password, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateWithDuplicatedNamesSucceds(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $user = $this->userService->insert($username, $password, $isActive);

        $id = $user->getId();

        $hasChanged = $this->userService->update($id, $username, $password, $isActive);

        $this->assertTrue($hasChanged);

        $hasChanged = $this->userService->update($id, $username, $password, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateSuccessfullyHappensWithTenUsers(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $users[$i] = $this->userService->insert($username . $i, $password . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $users[$i]->getId();
            $hasChanged = $this->userService->update($id, $username . $i, $password . $i, $isActive);
            $this->assertTrue($hasChanged);
        }
    }

    public function testIfUpdateFailsWithInvalidId(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $this->userService->insert($username, $password, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->update($id, $username, $password, $isActive);
    }

    public function testIfUpdateFailsWithEmptyUserName(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $user = $this->userService->insert($username, $password, $isActive);

        $username = "";
        $id = $user->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->update($id, $username, $password, $isActive);
    }

    public function testIfUpdateFailsWithEmptyPassWord(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $user = $this->userService->insert($username, $password, $isActive);

        $password = "";
        $id = $user->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->update($id, $username, $password, $isActive);
    }

    public function testIfSettingIsActiveWithSameValueFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $user = $this->userService->insert($username, $password, $isActive);

        $id = $user->getId();

        $hasChanged = $this->userService->setIsActive($id, $isActive);

        $this->assertFalse($hasChanged);
    }

    public function testIfSettingIsActiveWithDifferentValueSucceds(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $user = $this->userService->insert($username, $password, $isActive);

        $id = $user->getId();
        $isActive = false;

        $hasChanged = $this->userService->setIsActive($id, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfSettingIsActiveWithInvalidIdFails(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $this->userService->insert($username, $password, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->setIsActive($id, $isActive);
    }

    public function testIfItFindsByIdWithSuccess(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $user = $this->userService->insert($username, $password, $isActive);

        $id = $user->getId();

        $fetchedUser = $this->userService->findById($id);

        $this->assertEquals($user, $fetchedUser);
    }

    public function testIfItFindsByIdWithSuccessWithTenUsers(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $users[$i] = $this->userService->insert($username . $i, $password . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $users[$i]->getId();

            $fetchedUser = $this->userService->findById($id);

            $this->assertEquals($users[$i], $fetchedUser);
        }
    }

    public function testIfItCannotFindWithInvalidId(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $this->userService->insert($username, $password, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->findById($id);
    }

    public function testIfItFindsByUserNameWithSuccess(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $user = $this->userService->insert($username, $password, $isActive);

        $fetchedUserName = $user->getUsername();

        $fetchedUser = $this->userService->findByUserName($fetchedUserName);

        $this->assertEquals($user, $fetchedUser);
    }

    public function testIfItCannotFindByUserName(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $this->userService->insert($username, $password, $isActive);

        $fetchedUserName = "batata";

        $this->expectException(MockUnexistantRegisterException::class);

        $this->userService->findByUserName($fetchedUserName);
    }

    public function testIfItCannotFindWithEmptyUserName(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        $this->userService->insert($username, $password, $isActive);

        $username = "";

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->findByUserName($username);
    }

    public function testIfFindAllSuccedsWithZeroUsers(): void
    {
        $emptyArray = $this->userService->findAll();

        $this->assertEmpty($emptyArray);
    }

    public function testIfFindAllSuccedsWithTenUsers(): void
    {
        $username = "test";
        $password = "test";
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $this->userService->insert($username . $i, $password . $i, $isActive);
        }

        $allUsers = $this->userService->findAll();

        $this->assertNotEmpty($allUsers);
    }
}
