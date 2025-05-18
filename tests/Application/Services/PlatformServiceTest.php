<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockPlatformRepository;
use PHPUnit\Framework\TestCase;

class PlatformServiceTest extends TestCase
{
    //
    // Insert
    //

    public function testIfPlatformInsertionSuccedsWithTrueIsActive()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $platform = $platformService->insert('test', true);

        $this->assertInstanceOf(Platform::class, $platform);
    }

    public function testIfTenPlatformInsertionSuccedsWithTrueIsActive()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectNotToPerformAssertions();

        for ($i = 1; $i <= 10; $i++) {
            $platformService->insert('test' . $i, true);
        }
    }

    public function testIfPlatformInsertionSuccedsWithFalseIsActive()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $platform = $platformService->insert('test', false);

        $this->assertInstanceOf(Platform::class, $platform);
    }

    public function testIfTenPlatformInsertionSuccedsWithFalseIsActive()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectNotToPerformAssertions();

        for ($i = 1; $i <= 10; $i++) {
            $platformService->insert('test' . $i, false);
        }
    }

    public function testIfInsertionOfTwoPlatformWithTheSameNameFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(DatabaseDuplicatedEntryException::class);

        $platformService->insert('test', true);
        $platformService->insert('test', true);
    }

    //
    // Insert
    // - Name
    //

    public function testIfPlatformInsertionFailsWithEmptyName()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('', true);
    }

    public function testIfPlatformInsertionFailsWithNullName()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert(null, true);
    }

    public function testIfPlatformInsertionFailsWithArrayName()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert([], true);
    }

    public function testIfPlatformInsertionFailsWithNumberName()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert(1, true);
    }

    public function testIfPlatformInsertionFailsWithBooleanName()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert(true, true);
    }

    //
    // Insert
    // - Is Active
    //

    public function testIfPlatformInsertionFailsWithNullIsActive()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', null);
    }

    public function testIfPlatformInsertionFailsWithArrayIsActive()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', []);
    }

    public function testIfPlatformInsertionFailsWithStringIsActive()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', 'test');
    }

    public function testIfPlatformInsertionFailsWithNumberIsActive()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', 1);
    }

    //
    // Update
    //

    public function testIfUpdateSuccedsWithOnePlatformInTheRepository()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $platformService->insert('test', true);

        $this->assertTrue($platformService->update(1, 'test2', true));
    }

    public function testIfUpdateSuccedsWithTenPlatformsInTheRepository()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        for ($i = 1; $i <= 10; $i++) {
            $platformService->insert('test' . $i, true);
        }

        $this->assertTrue($platformService->update(1, 'test22', true));
    }

    public function testIfUpdatingAPlatformWithAExistantNameSucceds()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectNotToPerformAssertions();

        $platformService->insert('test', true);
        $platformService->update(1, 'test', true);
    }

    //
    // Update
    // - Id
    //

    public function testIfUpdatingAPlatformWithAUnexistantIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(-1, 'test', true);
    }

    public function testIfUpdatingAPlatformWithANullIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(null, 'test', true);
    }

    public function testIfUpdatingAPlatformWithAArrayIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update([], 'test', true);
    }

    public function testIfUpdatingAPlatformWithAStringIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update('test', 'test', true);
    }

    public function testIfUpdatingAPlatformWithABooleanIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(true, 'test', true);
    }

    //
    // Update
    // - Name
    //

    public function testIfUpdatingAPlatformWithAEmptyNameFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(1, '', true);
    }

    public function testIfUpdatingAPlatformWithANullNameFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(1, null, true);
    }

    public function testIfUpdatingAPlatformWithAArrayNameFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(1, [], true);
    }

    public function testIfUpdatingAPlatformWithANumberNameFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(1, 1, true);
    }

    public function testIfUpdatingAPlatformWithABooleanNameFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(1, true, true);
    }

    //
    // Update
    // - Is Active
    //

    public function testIfUpdatingAPlatformWithANullIsActiveFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(1, 'test', null);
    }

    public function testIfUpdatingAPlatformWithAArrayIsActiveFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(1, 'test', []);
    }

    public function testIfUpdatingAPlatformWithAStringIsActiveFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(1, 'test', 'test');
    }

    public function testIfUpdatingAPlatformWithANumberIsActiveFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->update(1, 'test', 1);
    }

    //
    // Set Is Active
    //

    public function testIfSettingAsActiveSucceds()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $platformService->insert('test', true);
        $this->assertTrue($platformService->setIsActive(1, false));
    }

    public function testIfSettingIsActiveWithSameValueFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $platformService->insert('test', true);
        $this->assertFalse($platformService->setIsActive(1, true));
    }

    //
    // Set Is Active
    // - Id
    //

    public function testIfSettingIsActiveWithUnexistantIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->setIsActive(-1, true);
    }

    public function testIfSettingIsActiveWithNullIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->setIsActive(null, true);
    }

    public function testIfSettingIsActiveWithArrayIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->setIsActive([], true);
    }

    public function testIfSettingIsActiveWithStringIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->setIsActive('test', true);
    }

    public function testIfSettingIsActiveWithBooleanIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->setIsActive(true, true);
    }

    //
    // Set Is Active
    // - Is Active
    //

    public function testIfSettingIsActiveWithNullValueFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->setIsActive(1, null);
    }

    public function testIfSettingIsActiveWithArrayValueFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->setIsActive(1, []);
    }

    public function testIfSettingIsActiveWithNumberValueFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->setIsActive(1, 1);
    }

    public function testIfSettingIsActiveWithStringValueFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->setIsActive(1, 'test');
    }

    //
    // Find By Id
    //

    public function testIfFindByIdSucceds()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $platformService->insert('test', true);
        $platform = $platformService->findById(1);

        $this->assertInstanceOf(Platform::class, $platform);
    }

    public function testIfFindByIdSuccedsWithTenPlatforms()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        for ($i = 1; $i <= 10; $i++) {
            $platformService->insert('test' . $i, true);
        }

        $platform = $platformService->findById(random_int(1, 10));

        $this->assertInstanceOf(Platform::class, $platform);
    }

    //
    // Find By Id
    // - Id
    //

    public function testIfFindByIdWithNullIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->findById(null);
    }

    public function testIfFindByIdWithArrayIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->findById([]);
    }

    public function testIfFindByIdWithStringIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->findById('test');
    }

    public function testIfFindByIdWithBooleanIdFails()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $this->expectException(EntityInvalidValueException::class);

        $platformService->insert('test', true);
        $platformService->findById('test');
    }

    //
    // Find All
    //

    public function testIfFindAllSuccedsEvenWithNoPlatformsInTheRepository()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $result = $platformService->findAll();

        $this->assertEmpty($result);
    }

    public function testIfFindAllSuccedsWithOnePlatformInTheRepository()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        $platformService->insert('test', true);
        $result = $platformService->findAll();

        $this->assertNotEmpty($result);
    }

    public function testIfFindAllSuccedsWithTenPlatformsInTheRepository()
    {
        $platformRepository = new MockPlatformRepository();
        $platformService = new PlatformService($platformRepository);

        for ($i = 1; $i <= 10; $i++) {
            $platformService->insert('test' . $i, true);
        }
        $result = $platformService->findAll();

        $this->assertNotEmpty($result);
    }
}
