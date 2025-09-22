<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\UserEntity;
use Mvreisg\GamebaseBackend\Domain\Exceptions\Entities\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserEntityRepository;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserEntityRepositoryInterface $userEntityRepository;
    private EncryptionInterface $encrypter;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userEntityRepository = new MockUserEntityRepository();
        $this->encrypter = new DefuseEncryption();
        $this->userService = new UserService($this->userEntityRepository, $this->encrypter);
    }

    public function testIfInsertSucceds(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $user = $this->userService->insert($userName, $passWord, $isActive);

        $this->assertNotEmpty($user);
        $this->assertInstanceOf(UserEntity::class, $user);
    }

    public function testIfInsertingUserNameThatAlreadyExistsFails(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $this->expectException(MockDuplicatedEntryException::class);

        $this->userService->insert($userName, $passWord, $isActive);
        $this->userService->insert($userName, $passWord, $isActive);
    }

    public function testIfInsertFailsWithEmptyUserName(): void
    {
        $userName = '';
        $passWord = 'test';
        $isActive = true;

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->insert($userName, $passWord, $isActive);
    }

    public function testIfInsertFailsWithEmptyPassword(): void
    {
        $userName = 'test';
        $passWord = '';
        $isActive = true;

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->insert($userName, $passWord, $isActive);
    }

    public function testIfUpdateSuccessfullyHappensWithOneUser(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $user = $this->userService->insert($userName, $passWord, $isActive);

        $id = $user->getId();

        $hasChanged = $this->userService->update($id, $userName, $passWord, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateWithDuplicatedNamesSucceds(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $user = $this->userService->insert($userName, $passWord, $isActive);

        $id = $user->getId();

        $hasChanged = $this->userService->update($id, $userName, $passWord, $isActive);

        $this->assertTrue($hasChanged);

        $hasChanged = $this->userService->update($id, $userName, $passWord, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateSuccessfullyHappensWithTenUsers(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $users[$i] = $this->userService->insert($userName . $i, $passWord . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $users[$i]->getId();
            $hasChanged = $this->userService->update($id, $userName . $i, $passWord . $i, $isActive);
            $this->assertTrue($hasChanged);
        }
    }

    public function testIfUpdateFailsWithInvalidId(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $this->userService->insert($userName, $passWord, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->update($id, $userName, $passWord, $isActive);
    }

    public function testIfUpdateFailsWithEmptyUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $user = $this->userService->insert($userName, $passWord, $isActive);

        $userName = '';
        $id = $user->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->update($id, $userName, $passWord, $isActive);
    }

    public function testIfUpdateFailsWithEmptyPassWord(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $user = $this->userService->insert($userName, $passWord, $isActive);

        $passWord = '';
        $id = $user->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->update($id, $userName, $passWord, $isActive);
    }

    public function testIfSettingIsActiveWithSameValueFails(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $user = $this->userService->insert($userName, $passWord, $isActive);

        $id = $user->getId();

        $hasChanged = $this->userService->setIsActive($id, $isActive);

        $this->assertFalse($hasChanged);
    }

    public function testIfSettingIsActiveWithDifferentValueSucceds(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $user = $this->userService->insert($userName, $passWord, $isActive);

        $id = $user->getId();
        $isActive = false;

        $hasChanged = $this->userService->setIsActive($id, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfSettingIsActiveWithInvalidIdFails(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $this->userService->insert($userName, $passWord, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->setIsActive($id, $isActive);
    }

    public function testIfItFindsByIdWithSuccess(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $user = $this->userService->insert($userName, $passWord, $isActive);

        $id = $user->getId();

        $fetchedUser = $this->userService->findById($id);

        $this->assertEquals($user, $fetchedUser);
    }

    public function testIfItFindsByIdWithSuccessWithTenUsers(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $users[$i] = $this->userService->insert($userName . $i, $passWord . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $users[$i]->getId();

            $fetchedUser = $this->userService->findById($id);

            $this->assertEquals($users[$i], $fetchedUser);
        }
    }

    public function testIfItCannotFindWithInvalidId(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $this->userService->insert($userName, $passWord, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->findById($id);
    }

    public function testIfItFindsByUserNameWithSuccess(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $user = $this->userService->insert($userName, $passWord, $isActive);

        $fetchedUserName = $user->getUserName();

        $fetchedUser = $this->userService->findByUserName($fetchedUserName);

        $this->assertEquals($user, $fetchedUser);
    }

    public function testIfItCannotFindByUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $this->userService->insert($userName, $passWord, $isActive);

        $fetchedUserName = 'batata';

        $this->expectException(MockUnexistantRegisterException::class);

        $this->userService->findByUserName($fetchedUserName);
    }

    public function testIfItCannotFindWithEmptyUserName(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        $this->userService->insert($userName, $passWord, $isActive);

        $userName = '';

        $this->expectException(EntityInvalidValueException::class);

        $this->userService->findByUserName($userName);
    }

    public function testIfFindAllSuccedsWithZeroUsers(): void
    {
        $emptyArray = $this->userService->findAll();

        $this->assertEmpty($emptyArray);
    }

    public function testIfFindAllSuccedsWithTenUsers(): void
    {
        $userName = 'test';
        $passWord = 'test';
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $this->userService->insert($userName . $i, $passWord . $i, $isActive);
        }

        $allUsers = $this->userService->findAll();

        $this->assertNotEmpty($allUsers);
    }
}
