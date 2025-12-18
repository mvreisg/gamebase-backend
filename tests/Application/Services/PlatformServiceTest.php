<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockPlatformRepository;
use PHPUnit\Framework\TestCase;

class PlatformServiceTest extends TestCase
{
    private PlatformRepositoryInterface $platformEntityRepository;
    private PlatformService $platformService;

    protected function setUp(): void
    {
        $this->platformEntityRepository = new MockPlatformRepository();
        $this->platformService = new PlatformService($this->platformEntityRepository);
    }

    public function testIfPlatformInsertionSucceds(): void
    {
        $name = "test";
        $isActive = true;

        $platform = $this->platformService->insert($name, $isActive);

        $this->assertNotEmpty($platform);
        $this->assertInstanceOf(Platform::class, $platform);
    }

    public function testIfTenPlatformsInsertionSucceds(): void
    {
        $name = "test";
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $platform = $this->platformService->insert($name . $i, $isActive);

            $this->assertNotEmpty($platform);
            $this->assertInstanceOf(Platform::class, $platform);
        }
    }

    public function testIfInsertionOfTwoPlatformsWithTheSameNameFails(): void
    {
        $name = "test";
        $isActive = true;

        $this->expectException(MockDuplicatedEntryException::class);

        $this->platformService->insert($name, $isActive);
        $this->platformService->insert($name, $isActive);
    }

    public function testIfPlatformInsertionFailsWithEmptyName(): void
    {
        $name = "";
        $isActive = true;

        $this->expectException(EntityInvalidValueException::class);

        $this->platformService->insert($name, $isActive);
    }

    public function testIfUpdateSucceds(): void
    {
        $name = "test";
        $isActive = true;

        $platform = $this->platformService->insert($name, $isActive);

        $id = $platform->getId();

        $hasChanged = $this->platformService->update($id, $name, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdateSuccedsWithTenPlatformsInTheRepository(): void
    {
        $name = "test";
        $isActive = true;

        $platforms = [];
        for ($i = 1; $i <= 10; $i++) {
            $platforms[$i] = $this->platformService->insert($name . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $platforms[$i]->getId();
            $hasChanged = $this->platformService->update($id, $name . $i, $isActive);
            $this->assertTrue($hasChanged);
        }
    }

    public function testIfUpdatingAPlatformWithAExistantNameSucceds(): void
    {
        $name = "test";
        $isActive = true;

        $platform = $this->platformService->insert($name, $isActive);

        $id = $platform->getId();

        $hasChanged = $this->platformService->update($id, $name, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfUpdatingAPlatformWithAUnexistantIdFails(): void
    {
        $name = "test";
        $isActive = true;

        $this->platformService->insert($name, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->platformService->update($id, $name, $isActive);
    }

    public function testIfUpdatingAPlatformWithAEmptyNameFails(): void
    {
        $name = "test";
        $isActive = true;

        $platform = $this->platformService->insert($name, $isActive);

        $name = "";
        $id = $platform->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->platformService->update($id, $name, $isActive);
    }

    public function testIfSettingAsActiveSucceds(): void
    {
        $name = "test";
        $isActive = false;

        $platform = $this->platformService->insert($name, $isActive);

        $isActive = true;
        $id = $platform->getId();

        $hasChanged = $this->platformService->setIsActive($id, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfSettingIsActiveWithSameValueFails(): void
    {
        $name = "test";
        $isActive = true;

        $platform = $this->platformService->insert($name, $isActive);

        $id = $platform->getId();

        $hasChanged = $this->platformService->setIsActive($id, $isActive);

        $this->assertFalse($hasChanged);
    }

    public function testIfSettingIsActiveWithInvalidIdFails(): void
    {
        $name = "test";
        $isActive = true;

        $this->platformService->insert($name, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->platformService->setIsActive($id, $isActive);
    }

    public function testIfFindByIdSucceds(): void
    {
        $name = "test";
        $isActive = true;

        $platform = $this->platformService->insert($name, $isActive);

        $id = $platform->getId();

        $platform = $this->platformService->findById($id);

        $this->assertNotEmpty($platform);
        $this->assertInstanceOf(Platform::class, $platform);
    }

    public function testIfFindByIdSuccedsWithTenPlatforms(): void
    {
        $name = "test";
        $isActive = true;

        $platforms = [];
        for ($i = 1; $i <= 10; $i++) {
            $platforms[$i] = $this->platformService->insert($name . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $platforms[$i]->getId();

            $platform = $this->platformService->findById($id);

            $this->assertNotEmpty($platform);
            $this->assertInstanceOf(Platform::class, $platform);
        }
    }

    public function testIfFindAllSuccedsEvenWithNoPlatformsInTheRepository(): void
    {
        $emptyArray = $this->platformService->findAll();

        $this->assertEmpty($emptyArray);
    }

    public function testIfFindAllSuccedsWithOnePlatformInTheRepository(): void
    {
        $name = "test";
        $isActive = true;

        $this->platformService->insert($name, $isActive);

        $platformsArray = $this->platformService->findAll();

        $this->assertNotEmpty($platformsArray);
    }

    public function testIfFindAllSuccedsWithTenPlatformsInTheRepository(): void
    {
        $name = "test";
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $this->platformService->insert($name . $i, $isActive);
        }

        $platformsArray = $this->platformService->findAll();

        $this->assertNotEmpty($platformsArray);
    }
}
