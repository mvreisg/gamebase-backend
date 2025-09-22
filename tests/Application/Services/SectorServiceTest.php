<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorEntity;
use Mvreisg\GamebaseBackend\Domain\Exceptions\Entities\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockSectorEntityRepository;
use PHPUnit\Framework\TestCase;

class SectorServiceTest extends TestCase
{
    private SectorEntityRepositoryInterface $sectorEntityRepository;
    private SectorService $sectorService;

    protected function setUp(): void
    {
        $this->sectorEntityRepository = new MockSectorEntityRepository();
        $this->sectorService = new SectorService($this->sectorEntityRepository);
    }

    public function testIfASingleInsertionSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $sector = $this->sectorService->insert($name, $isActive);

        $this->assertNotEmpty($sector);
        $this->assertInstanceOf(SectorEntity::class, $sector);
    }

    public function testIfASingleInsertionWithInvalidNameFails(): void
    {
        $name = '';
        $isActive = true;

        $this->expectException(EntityInvalidValueException::class);

        $this->sectorService->insert($name, $isActive);
    }

    public function testIfDuplicatedInsertionsFails(): void
    {
        $name = 'test';
        $isActive = true;

        $this->expectException(MockDuplicatedEntryException::class);

        $this->sectorService->insert($name, $isActive);
        $this->sectorService->insert($name, $isActive);
    }

    public function testIfTenInsertionsSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $sector = $this->sectorService->insert($name . $i, $isActive);

            $this->assertNotEmpty($sector);
            $this->assertInstanceOf(SectorEntity::class, $sector);
        }
    }

    public function testIfUpdateSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $sector = $this->sectorService->insert($name, $isActive);

        $name = 'teste';
        $id = $sector->getId();

        $hasChanged = $this->sectorService->update($id, $name, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateSuccedsWithSameValues(): void
    {
        $name = 'test';
        $isActive = true;

        $sector = $this->sectorService->insert($name, $isActive);

        $id = $sector->getId();

        $hasChanged = $this->sectorService->update($id, $name, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateWithInvalidNameFails(): void
    {
        $name = 'test';
        $isActive = true;

        $sector = $this->sectorService->insert($name, $isActive);

        $name = '';
        $id = $sector->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->sectorService->update($id, $name, $isActive);
    }

    public function testIfSetIsActiveSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $sector = $this->sectorService->insert($name, $isActive);
        $id = $sector->getId();
        $isActive = false;

        $hasChanged = $this->sectorService->setIsActive($id, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfSetIsActiveDoesNotChangeRepositoryState(): void
    {
        $name = 'test';
        $isActive = true;

        $sector = $this->sectorService->insert($name, $isActive);
        $id = $sector->getId();

        $hasChanged = $this->sectorService->setIsActive($id, $isActive);

        $this->assertFalse($hasChanged);
    }

    public function testIfFindByIdSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $sector = $this->sectorService->insert($name, $isActive);
        $id = $sector->getId();

        $fetchedPermission = $this->sectorService->findById($id);

        $this->assertNotEmpty($fetchedPermission);
        $this->assertInstanceOf(SectorEntity::class, $fetchedPermission);
        $this->assertEquals($sector, $fetchedPermission);
    }

    public function testIfFindByIdWithInvalidIdFails(): void
    {
        $name = 'test';
        $isActive = true;

        $this->sectorService->insert($name, $isActive);
        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->sectorService->findById($id);
    }

    public function testIfFindAllReturnsEmpty(): void
    {
        $emptyArray = $this->sectorService->findAll();

        $this->assertEmpty($emptyArray);
    }

    public function testIfFindAllReturnsFilled(): void
    {
        $name = 'test';
        $isActive = true;

        $this->sectorService->insert($name, $isActive);

        $filledArray = $this->sectorService->findAll();

        $this->assertNotEmpty($filledArray);
    }
}
