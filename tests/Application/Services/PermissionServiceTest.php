<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockPermissionRepository;
use PHPUnit\Framework\TestCase;

class PermissionServiceTest extends TestCase
{
    private PermissionRepositoryInterface $permissionRepository;
    private PermissionService $permissionService;

    protected function setUp(): void
    {
        $this->permissionRepository = new MockPermissionRepository();
        $this->permissionService = new PermissionService($this->permissionRepository);
    }

    public function testIfASingleInsertionSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $permission = $this->permissionService->insert($name, $isActive);

        $this->assertNotEmpty($permission);
        $this->assertInstanceOf(Permission::class, $permission);
    }

    public function testIfASingleInsertionWithInvalidNameFails(): void
    {
        $name = '';
        $isActive = true;

        $this->expectException(EntityInvalidValueException::class);

        $this->permissionService->insert($name, $isActive);
    }

    public function testIfDuplicatedInsertionsFails(): void
    {
        $name = 'test';
        $isActive = true;

        $this->expectException(DatabaseDuplicatedEntryException::class);

        $this->permissionService->insert($name, $isActive);
        $this->permissionService->insert($name, $isActive);
    }    

    public function testIfTenInsertionsSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $permission = $this->permissionService->insert($name . $i, $isActive);

            $this->assertNotEmpty($permission);
            $this->assertInstanceOf(Permission::class, $permission);
        }
    }

    public function testIfUpdateSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $permission = $this->permissionService->insert($name, $isActive);

        $name = 'teste';
        $id = $permission->getId();

        $hasChanged = $this->permissionService->update($id, $name, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateSuccedsWithSameValues(): void
    {
        $name = 'test';
        $isActive = true;

        $permission = $this->permissionService->insert($name, $isActive);

        $id = $permission->getId();

        $hasChanged = $this->permissionService->update($id, $name, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateWithInvalidNameFails(): void
    {
        $name = 'test';
        $isActive = true;

        $permission = $this->permissionService->insert($name, $isActive);

        $name = '';
        $id = $permission->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->permissionService->update($id, $name, $isActive);
    }

    public function testIfSetIsActiveSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $permission = $this->permissionService->insert($name, $isActive);
        $id = $permission->getId();
        $isActive = false;

        $hasChanged = $this->permissionService->setIsActive($id, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfSetIsActiveDoesNotChangeRepositoryState(): void
    {
        $name = 'test';
        $isActive = true;

        $permission = $this->permissionService->insert($name, $isActive);
        $id = $permission->getId();

        $hasChanged = $this->permissionService->setIsActive($id, $isActive);

        $this->assertFalse($hasChanged);
    }

    public function testIfFindByIdSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $permission = $this->permissionService->insert($name, $isActive);
        $id = $permission->getId();

        $fetchedPermission = $this->permissionService->findById($id);

        $this->assertNotEmpty($fetchedPermission);
        $this->assertInstanceOf(Permission::class, $fetchedPermission);
        $this->assertEquals($permission, $fetchedPermission);
    }

    public function testIfFindByIdWithInvalidIdFails(): void
    {
        $name = 'test';
        $isActive = true;

        $this->permissionService->insert($name, $isActive);
        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->permissionService->findById($id);
    }

    public function testIfFindAllReturnsEmpty(): void
    {
        $emptyArray = $this->permissionService->findAll();

        $this->assertEmpty($emptyArray);
    }

    public function testIfFindAllReturnsFilled(): void
    {
        $name = 'test';
        $isActive = true;

        $this->permissionService->insert($name, $isActive);

        $filledArray = $this->permissionService->findAll();

        $this->assertNotEmpty($filledArray);
    }
}
