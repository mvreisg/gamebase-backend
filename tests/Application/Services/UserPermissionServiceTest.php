<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use ArrayIterator;
use Mvreisg\GamebaseBackend\Application\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\UserPermissionEntity;
use Mvreisg\GamebaseBackend\Domain\Exceptions\Entities\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\UserPermissionEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockPermissionEntityRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserEntityRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockUserPermissionEntityRepository;
use PHPUnit\Framework\TestCase;

class UserPermissionServiceTest extends TestCase
{
    private UserPermissionEntityRepositoryInterface $userPermissionEntityRepository;
    private UserPermissionService $userPermissionService;
    private UserEntityRepositoryInterface $userEntityRepository;
    private UserService $userService;
    private EncryptionInterface $encrypter;
    private PermissionEntityRepositoryInterface $permissionEntityRepository;
    private PermissionService $permissionService;

    protected function setUp(): void
    {
        $this->userEntityRepository = new MockUserEntityRepository();
        $this->encrypter = new DefuseEncryption();
        $this->userService = new UserService($this->userEntityRepository, $this->encrypter);
        $this->permissionEntityRepository = new MockPermissionEntityRepository();
        $this->permissionService = new PermissionService($this->permissionEntityRepository);
        $this->userPermissionEntityRepository = new MockUserPermissionEntityRepository(
            $this->userEntityRepository,
            $this->permissionEntityRepository
        );
        $this->userPermissionService = new UserPermissionService($this->userPermissionEntityRepository);
    }

    public function testIfInsertionSucceds(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->userPermissionService->insert($userId, $permissionId);

        $this->assertNotEmpty($userPermission);
        $this->assertInstanceOf(UserPermissionEntity::class, $userPermission);
    }

    public function testIfTenInsertionsSucceds(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = $this->userService->insert('test' . $i, 'test' . $i, true);
            $userId = $user->getId();

            $permission = $this->permissionService->insert('test' . $i, true);
            $permissionId = $permission->getId();

            $userPermission = $this->userPermissionService->insert($userId, $permissionId);

            $this->assertNotEmpty($userPermission);
            $this->assertInstanceOf(UserPermissionEntity::class, $userPermission);
        }
    }

    public function testIfInsertionWithInvalidUserIdFails(): void
    {
        $userId = -1;

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->userPermissionService->insert($userId, $permissionId);
    }

    public function testIfInsertionWithUnexistantUserIdFails(): void
    {
        $userId = 999;

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->expectException(MockUnexistantRegisterException::class);

        $this->userPermissionService->insert($userId, $permissionId);
    }

    public function testIfInsertionWithInvalidPermissionIdFails(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permissionId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userPermissionService->insert($userId, $permissionId);
    }

    public function testIfUpdateSucceds(): void
    {
        $users = [];
        $permissions = [];
        for ($i = 1; $i <= 2; $i++) {
            $users[] = $this->userService->insert('test' . $i, 'test' . $i, true);
            $permissions[] = $this->permissionService->insert('test' . $i, true);
        }

        $usersIterator = new ArrayIterator($users);
        $permissionsIterator = new ArrayIterator($permissions);

        $userId = $usersIterator->current()->getId();
        $usersIterator->next();

        $permissionId = $permissionsIterator->current()->getId();
        $permissionsIterator->next();

        $userPermission = $this->userPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();

        $userId = $usersIterator->current()->getId();
        $permissionId = $permissionsIterator->current()->getId();

        $hasChanged = $this->userPermissionService->update($id, $userId, $permissionId);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateFailsWithSameValues(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->userPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();

        $hasChanged = $this->userPermissionService->update($id, $userId, $permissionId);

        $this->assertFalse($hasChanged);
    }

    public function testIfUpdateWithInvalidIdFails(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->userPermissionService->insert($userId, $permissionId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userPermissionService->update($id, $userId, $permissionId);
    }

    public function testIfUpdateWithInvalidUserIdFails(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->userPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();
        $userId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userPermissionService->update($id, $userId, $permissionId);
    }

    public function testIfUpdateWithInvalidPermissionIdFails(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->userPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();
        $permissionId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userPermissionService->update($id, $userId, $permissionId);
    }

    public function testIfDeletionSucceds(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->userPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();

        $hasDeleted = $this->userPermissionService->delete($id);

        $this->assertTrue($hasDeleted);
    }

    public function testIfDeletionWithUnexistantIdFails(): void
    {
        $id = 1;

        $hasDeleted = $this->userPermissionService->delete($id);

        $this->assertFalse($hasDeleted);
    }

    public function testIfDeletionWithInvalidIdSucceds(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->userPermissionService->insert($userId, $permissionId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userPermissionService->delete($id);
    }

    public function testIfFindByIdSucceds(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->userPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();

        $fetchedUserPermission = $this->userPermissionService->findById($id);

        $this->assertNotEmpty($fetchedUserPermission);
        $this->assertInstanceOf(UserPermissionEntity::class, $fetchedUserPermission);
        $this->assertEquals($userPermission, $fetchedUserPermission);
    }

    public function testIfFindByIdWithInvalidIdFails(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->userPermissionService->insert($userId, $permissionId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->userPermissionService->findById($id);
    }

    public function testIfFindByIdWithUnexistantIdFails(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->userPermissionService->insert($userId, $permissionId);

        $id = 999;

        $fetchedUserPermission = $this->userPermissionService->findById($id);

        $this->assertEmpty($fetchedUserPermission);
    }

    public function testIfFindAllSucceds(): void
    {
        $user = $this->userService->insert('test', 'test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->userPermissionService->insert($userId, $permissionId);

        $userPermissions = $this->userPermissionService->findAll();

        $this->assertNotEmpty($userPermissions);
    }

    public function testIfFindAllWithNoRegistersSucceds(): void
    {
        $emptyUserPermissionsArray = $this->userPermissionService->findAll();

        $this->assertEmpty($emptyUserPermissionsArray);
    }
}
