<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use PHPUnit\Framework\TestCase;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserRepository;

class UserServiceTest extends TestCase
{
    //
    // Insert
    //

    public function testIfInsertSuccessfullyReturnsAUserObject()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $user = $userService->insert('test', 'test', true);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testIfInsertWithDuplicatedNamesFails()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(DatabaseDuplicatedEntryException::class);

        $userService->insert('test', 'test', true);
        $userService->insert('test', 'test', true);
    }

    //
    // Insert
    // - Name
    //

    public function testIfInsertFailsWithNullName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert(null, 'test', true);
    }

    public function testIfInsertFailsWithArrayName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert([], 'test', true);
    }

    public function testIfInsertFailsWithEmptyNameString()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('', 'test', true);
    }

    public function testIfInsertFailsWithANumberOnNameParameter()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert(1, 'test', true);
    }

    public function testIfInsertFailsWithABooleanOnNameParameter()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert(true, 'test', true);
    }

    //
    // Insert
    // - Password
    //

    public function testIfInsertFailsWithNullPassword()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', null, true);
    }

    public function testIfInsertFailsWithArrayPassword()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', [], true);
    }

    public function testIfInsertFailsWithEmptyStringPassword()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', '', true);
    }

    public function testIfInsertFailsWithANumberOnPasswordParameter()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 1, true);
    }

    public function testIfInsertFailsWithABooleanOnPasswordParameter()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', true, true);
    }

    //
    // Insert
    // - Is Active
    //

    public function testIfInsertFailsWithNullIsActive()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', null);
    }

    public function testIfInsertFailsWithArrayIsActive()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', null);
    }

    public function testIfInsertFailsWithNumberIsActive()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', 1);
    }

    public function testIfInsertFailsWithStringIsActive()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', 'test');
    }

    //
    // Update
    //

    public function testIfUpdateSuccessfullyHappensWithOneUser()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertTrue($userService->update(1, 'test2', 'test2', true));
    }

    public function testIfUpdateWithDuplicatedNamesSucceds()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectNotToPerformAssertions();

        $userService->insert('test', 'test', true);
        $userService->update(1, 'test', 'test', true);
    }

    public function testIfUpdateSuccessfullyHappensWithTwoUsers()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $userService->insert('test2', 'test2', true);
        $this->assertTrue($userService->update(2, 'test3', 'test3', true));
    }

    public function testIfUpdateSuccessfullyHappensWithTenUsers()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        for ($i = 0; $i < 10; $i++) {
            $userService->insert('test' . $i, 'test' . $i, true);
        }
        $this->assertTrue($userService->update(2, 'test22', 'test22', true));
    }

    //
    // Update
    // - Id
    //

    public function testIfUpdateFailsWithUnexistantId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $result = $userService->update(2, 'test2', 'test2', true);

        $this->assertFalse($result);
    }

    public function testIfUpdateFailsWithInvalidId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(0, 'test2', 'test2', true);
    }

    public function testIfUpdateFailsWithNullId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(null, 'test2', 'test2', true);
    }

    public function testIfUpdateFailsWithArrayId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update([], 'test2', 'test2', true);
    }

    public function testIfUpdateFailsWithStringId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update('test', 'test2', 'test2', true);
    }

    public function testIfUpdateFailsWithBooleanId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(true, 'test2', 'test2', true);
    }

    //
    // Update
    // - Name
    //

    public function testIfUpdateFailsWithNullName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, null, 'test2', true);
    }

    public function testIfUpdateFailsWithNumberName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, 1, 'test2', true);
    }

    public function testIfUpdateFailsWithArrayName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, [], 'test2', true);
    }

    public function testIfUpdateFailsWithEmptyName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, '', 'test2', true);
    }

    public function testIfUpdateFailsWithBooleanName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, true, 'test2', true);
    }

    //
    // Update
    // - Password
    //

    public function testIfUpdateFailsWithNullPassword()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, 'test2', null, true);
    }

    public function testIfUpdateFailsWithNumberPassword()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, 'test2', 1, true);
    }

    public function testIfUpdateFailsWithArrayPassword()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, 'test2', [], true);
    }

    public function testIfUpdateFailsWithEmptyPassword()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, 'test2', '', true);
    }

    public function testIfUpdateFailsWithBooleanPassword()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, 'test2', true, true);
    }

    //
    // Update
    // - Is Active
    //

    public function testIfUpdateFailsWithNullIsActive()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, 'test2', 'test2', null);
    }

    public function testIfUpdateFailsWithNumberIsActive()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, 'test2', 'test2', 1);
    }

    public function testIfUpdateFailsWithArrayIsActive()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, 'test2', 'test2', []);
    }

    public function testIfUpdateFailsWithStringPassword()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->update(1, 'test2', 'test2', 'test2');
    }

    //
    // Set Is Active
    //

    public function testIfSettingIsActiveWithSameValueFails()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertFalse($userService->setIsActive(1, true));
    }

    public function testIfSettingIsActiveWithDifferentValueSucceds()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertTrue($userService->setIsActive(1, false));
    }

    //
    // Set Is Active
    // - Id
    //

    public function testIfSettingIsActiveWithInvalidIdFails()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->setIsActive(0, false);
    }

    public function testIfSettingIsActiveWithNonExistantIdFails()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $result = $userService->setIsActive(2, false);

        $this->assertFalse($result);
    }

    public function testIfSettingIsActiveWithNullIdFails()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->setIsActive(null, false);
    }

    public function testIfSettingIsActiveWithStringIdFails()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->setIsActive('test', false);
    }

    public function testIfSettingIsActiveWithBooleanIdFails()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->setIsActive(true, false);
    }

    public function testIfSettingIsActiveWithArrayIdFails()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->setIsActive([], false);
    }

    //
    // Set Is Active
    // - Is Active
    //

    public function testIfSettingIsActiveWithNullValue()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->setIsActive(1, null);
    }

    public function testIfSettingIsActiveWithNumberValue()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->setIsActive(1, 1);
    }

    public function testIfSettingIsActiveWithArrayValue()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->setIsActive(1, []);
    }

    public function testIfSettingIsActiveWithStringValue()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->setIsActive(1, 'test');
    }

    //
    // Find By Id
    //

    public function testIfItFindsByIdWithSuccess()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertInstanceOf(User::class, $userService->findById(1));
    }

    public function testIfItFindsByIdWithSuccessWithTenUsers()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        for ($i = 0; $i < 10; $i++) {
            $userService->insert('test' . $i, 'test' . $i, true);
        }
        $this->assertInstanceOf(User::class, $userService->findById(random_int(1, 10)));
    }

    public function testIfItCannotFindWithUnexistantId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertNull($userService->findById(2));
    }

    public function testIfItCannotFindWithInvalidId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->findById(0);
    }

    public function testIfItCannotFindWithNullId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->findById(null);
    }

    public function testIfItCannotFindWithStringId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->findById('test');
    }

    public function testIfItCannotFindWithBooleanId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->findById(true);
    }

    public function testIfItCannotFindWithArrayId()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->findById([]);
    }

    //
    // Find By UserName
    //

    public function testIfItFindsByUserNameWithSuccess()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertInstanceOf(User::class, $userService->findByUserName('test'));
    }

    public function testIfItCannotFindByUserName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $userService->insert('test', 'test', true);
        $this->assertNull($userService->findByUserName('test2'));
    }

    public function testIfItCannotFindWithEmptyUserName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->findByUserName('');
    }

    public function testIfItCannotFindWithNullUserName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->findByUserName(null);
    }

    public function testIfItCannotFindWithArrayUserName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->findByUserName([]);
    }

    public function testIfItCannotFindWithNumberUserName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->findByUserName(1);
    }

    public function testIfItCannotFindWithBooleanUserName()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->expectException(EntityInvalidValueException::class);

        $userService->insert('test', 'test', true);
        $userService->findByUserName(true);
    }

    //
    // Find All
    //

    public function testIfFindAllSuccedsWithZeroUsers()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        $this->assertEmpty($userService->findAll());
    }

    public function testIfFindAllSuccedsWithTenUsers()
    {
        $userRepository = new MockUserRepository();
        $encrypter = new DefuseEncryption();
        $userService = new UserService($userRepository, $encrypter);

        for ($i = 0; $i < 10; $i++) {
            $userService->insert('test' . $i, 'test' . $i, true);
        }
        $this->assertNotEmpty($userService->findAll());
    }
}
