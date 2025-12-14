<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use ArrayIterator;
use Mvreisg\GamebaseBackend\Domain\Encryption\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorPermissionInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockPermissionRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockSectorRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockSectorPermissionRepository;
use PHPUnit\Framework\TestCase;

class SectorPermissionServiceTest extends TestCase
{
    private SectorPermissionInterface $sectorPermissionEntityRepository;
    private SectorPermissionService $sectorPermissionService;
    private SectorRepositoryInterface $sectorEntityRepository;
    private SectorService $sectorService;
    private EncryptionInterface $encrypter;
    private PermissionRepositoryInterface $permissionEntityRepository;
    private PermissionService $permissionService;

    protected function setUp(): void
    {
        $this->sectorEntityRepository = new MockSectorRepository();
        $this->encrypter = new DefuseEncryption();
        $this->sectorService = new SectorService($this->sectorEntityRepository, $this->encrypter);
        $this->permissionEntityRepository = new MockPermissionRepository();
        $this->permissionService = new PermissionService($this->permissionEntityRepository);
        $this->sectorPermissionEntityRepository = new MockSectorPermissionRepository(
            $this->sectorEntityRepository,
            $this->permissionEntityRepository
        );
        $this->sectorPermissionService = new SectorPermissionService($this->sectorPermissionEntityRepository);
    }

    public function testIfInsertionSucceds(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->sectorPermissionService->insert($userId, $permissionId);

        $this->assertNotEmpty($userPermission);
        $this->assertInstanceOf(SectorPermission::class, $userPermission);
    }

    public function testIfTenInsertionsSucceds(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = $this->sectorService->insert('test' . $i, true);
            $userId = $user->getId();

            $permission = $this->permissionService->insert('test' . $i, true);
            $permissionId = $permission->getId();

            $userPermission = $this->sectorPermissionService->insert($userId, $permissionId);

            $this->assertNotEmpty($userPermission);
            $this->assertInstanceOf(SectorPermission::class, $userPermission);
        }
    }

    public function testIfInsertionWithInvalidUserIdFails(): void
    {
        $userId = -1;

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->sectorPermissionService->insert($userId, $permissionId);
    }

    public function testIfInsertionWithUnexistantUserIdFails(): void
    {
        $userId = 999;

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->expectException(MockUnexistantRegisterException::class);

        $this->sectorPermissionService->insert($userId, $permissionId);
    }

    public function testIfInsertionWithInvalidPermissionIdFails(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permissionId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->sectorPermissionService->insert($userId, $permissionId);
    }

    public function testIfUpdateSucceds(): void
    {
        $users = [];
        $permissions = [];
        for ($i = 1; $i <= 2; $i++) {
            $users[] = $this->sectorService->insert('test' . $i, true);
            $permissions[] = $this->permissionService->insert('test' . $i, true);
        }

        $usersIterator = new ArrayIterator($users);
        $permissionsIterator = new ArrayIterator($permissions);

        $userId = $usersIterator->current()->getId();
        $usersIterator->next();

        $permissionId = $permissionsIterator->current()->getId();
        $permissionsIterator->next();

        $userPermission = $this->sectorPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();

        $userId = $usersIterator->current()->getId();
        $permissionId = $permissionsIterator->current()->getId();

        $hasChanged = $this->sectorPermissionService->update($id, $userId, $permissionId);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateFailsWithSameValues(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->sectorPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();

        $hasChanged = $this->sectorPermissionService->update($id, $userId, $permissionId);

        $this->assertFalse($hasChanged);
    }

    public function testIfUpdateWithInvalidIdFails(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->sectorPermissionService->insert($userId, $permissionId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->sectorPermissionService->update($id, $userId, $permissionId);
    }

    public function testIfUpdateWithInvalidUserIdFails(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->sectorPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();
        $userId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->sectorPermissionService->update($id, $userId, $permissionId);
    }

    public function testIfUpdateWithInvalidPermissionIdFails(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->sectorPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();
        $permissionId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->sectorPermissionService->update($id, $userId, $permissionId);
    }

    public function testIfDeletionSucceds(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->sectorPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();

        $hasDeleted = $this->sectorPermissionService->delete($id);

        $this->assertTrue($hasDeleted);
    }

    public function testIfDeletionWithUnexistantIdFails(): void
    {
        $id = 1;

        $hasDeleted = $this->sectorPermissionService->delete($id);

        $this->assertFalse($hasDeleted);
    }

    public function testIfDeletionWithInvalidIdSucceds(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->sectorPermissionService->insert($userId, $permissionId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->sectorPermissionService->delete($id);
    }

    public function testIfFindByIdSucceds(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $userPermission = $this->sectorPermissionService->insert($userId, $permissionId);

        $id = $userPermission->getId();

        $fetchedUserPermission = $this->sectorPermissionService->findById($id);

        $this->assertNotEmpty($fetchedUserPermission);
        $this->assertInstanceOf(SectorPermission::class, $fetchedUserPermission);
        $this->assertEquals($userPermission, $fetchedUserPermission);
    }

    public function testIfFindByIdWithInvalidIdFails(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->sectorPermissionService->insert($userId, $permissionId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->sectorPermissionService->findById($id);
    }

    public function testIfFindByIdWithUnexistantIdFails(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->sectorPermissionService->insert($userId, $permissionId);

        $id = 999;

        $fetchedUserPermission = $this->sectorPermissionService->findById($id);

        $this->assertEmpty($fetchedUserPermission);
    }

    public function testIfFindAllSucceds(): void
    {
        $user = $this->sectorService->insert('test', true);
        $userId = $user->getId();

        $permission = $this->permissionService->insert('test', true);
        $permissionId = $permission->getId();

        $this->sectorPermissionService->insert($userId, $permissionId);

        $userPermissions = $this->sectorPermissionService->findAll();

        $this->assertNotEmpty($userPermissions);
    }

    public function testIfFindAllWithNoRegistersSucceds(): void
    {
        $emptyUserPermissionsArray = $this->sectorPermissionService->findAll();

        $this->assertEmpty($emptyUserPermissionsArray);
    }
}
