<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGamePlatformRepository;
use PHPUnit\Framework\TestCase;

class GamePlatformServiceTest extends TestCase
{
    //
    // Insert
    //

    public function testIfItSuccessfullyInserts()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $result = $service->insert(1, 1);
        $this->assertInstanceOf(GamePlatform::class, $result);
    }

    public function testIfItSuccessfullyInsertsTenRegisters()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectNotToPerformAssertions();

        for ($i = 1; $i <= 10; $i++) {
            $service->insert($i, $i);
        }
    }

    //
    // Insert
    // - Platform Id
    //

    public function testIfItFailsToInsertWithInvalidPlatformId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(-1, 1);
    }

    public function testIfItFailsToInsertWithNullPlatformId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(null, 1);
    }

    public function testIfItFailsToInsertWithArrayPlatformId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert([], 1);
    }

    public function testIfItFailsToInsertWithStringPlatformId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert('test', 1);
    }

    public function testIfItFailsToInsertWithBooleanPlatformId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(true, 1);
    }

    //
    // Insert
    // - Game Id
    //

    public function testIfItFailsToInsertWithInvalidGameId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, -1);
    }

    public function testIfItFailsToInsertWithNullGameId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, null);
    }

    public function testIfItFailsToInsertWithArrayGameId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, []);
    }

    public function testIfItFailsToInsertWithStringGameId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 'test');
    }

    public function testIfItFailsToInsertWithBooleanGameId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, true);
    }

    //
    // Update
    //

    public function testIfItSuccessfullyUpdates()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $service->insert(1, 1);
        $result = $service->update(1, 1, 1);

        $this->assertTrue($result);
    }

    public function testIfItSuccessfullyUpdatesWithTenRegisters()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        for ($i = 1; $i <= 10; $i++) {
            $service->insert($i, $i);
        }
        $random = random_int(1, 10);
        $result = $service->update($random, $random, $random);

        $this->assertTrue($result);
    }

    public function testIfItFailsToUpdateWithTenRegisters()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        for ($i = 1; $i <= 10; $i++) {
            $service->insert($i, $i);
        }

        $result = $service->update(11, 1, 1);

        $this->assertFalse($result);
    }

    //
    // Update
    // - Id
    //

    public function testIfItFailsToUpdateWithInvalidId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(-1, 1, 1);
    }

    public function testIfItFailsToUpdateWithNullId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(null, 1, 1);
    }

    public function testIfItFailsToUpdateWithArrayId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update([], 1, 1);
    }

    public function testIfItFailsToUpdateWithStringId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update('test', 1, 1);
    }

    public function testIfItFailsToUpdateWithBooleanId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(true, 1, 1);
    }

    //
    // Update
    // - Platform Id
    //

    public function testIfItFailsToUpdateWithInvalidPlatformId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(1, -1, 1);
    }

    public function testIfItFailsToUpdateWithNullPlatformId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(1, null, 1);
    }

    public function testIfItFailsToUpdateWithArrayPlatformId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(1, [], 1);
    }

    public function testIfItFailsToUpdateWithStringPlatformId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(1, 'test', 1);
    }

    public function testIfItFailsToUpdateWithBooleanPlatformId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(1, true, 1);
    }

    //
    // Update
    // - Game Id
    //

    public function testIfItFailsToUpdateWithInvalidGameId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(1, 1, -1);
    }

    public function testIfItFailsToUpdateWithNullGameId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(1, 1, null);
    }

    public function testIfItFailsToUpdateWithArrayGameId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(1, 1, []);
    }

    public function testIfItFailsToUpdateWithStringGameId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(1, 1, 'test');
    }

    public function testIfItFailsToUpdateWithBooleanGameId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->update(1, 1, true);
    }

    //
    // Delete
    //

    public function testIfItSuccessfullyDeletes()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $service->insert(1, 1);
        $result = $service->delete(1);

        $this->assertTrue($result);
    }

    public function testIfItSuccessfullyDeletesWithTenRegisters()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        for ($i = 1; $i <= 10; $i++) {
            $service->insert($i, $i);
        }
        $result = $service->delete(random_int(1, 10));

        $this->assertTrue($result);
    }

    public function testIfIFailsToDeleteWithTenRegisters()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        for ($i = 1; $i <= 10; $i++) {
            $service->insert($i, $i);
        }
        $result = $service->delete(11);

        $this->assertFalse($result);
    }

    public function testIfItFailsToDeleteWithInvalidId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->delete(-1);
    }

    public function testIfItFailsToDeleteWithNullId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->delete(null);
    }

    public function testIfItFailsToDeleteWithArrayId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->delete([]);
    }

    public function testIfItFailsToDeleteWithStringId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->delete('test');
    }

    public function testIfItFailsToDeleteWithBooleanId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->delete(true);
    }

    //
    // Find By Id
    //

    public function testIfItSucessfullyFindsById()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $service->insert(1, 1);
        $result = $service->findById(1);

        $this->assertInstanceOf(GamePlatform::class, $result);
    }

    public function testIfItSucessfullyFindsByIdWithTenRegisters()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        for ($i = 1; $i <= 10; $i++) {
            $service->insert($i, $i);
        }
        $result = $service->findById(random_int(1, 10));

        $this->assertInstanceOf(GamePlatform::class, $result);
    }

    public function testIfItFailsToFindByIdWithUnexistantId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $service->insert(1, 1);
        $result = $service->findById(2);

        $this->assertEmpty($result);
    }

    public function testIfItFailsToFindByIdWithUnexistantIdWithTenRegisters()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        for ($i = 1; $i <= 10; $i++) {
            $service->insert($i, $i);
        }
        $result = $service->findById(11);

        $this->assertEmpty($result);
    }

    public function testIfItFailsToFindByIdWithInvalidId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->findById(-1);
    }

    public function testIfItFailsToFindByIdWithNullId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->findById(null);
    }

    public function testIfItFailsToFindByIdWithArrayId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->findById([]);
    }

    public function testIfItFailsToFindByIdWithStringId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->findById('test');
    }

    public function testIfItFailsToFindByIdWithBooleanId()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $this->expectException(EntityInvalidValueException::class);

        $service->insert(1, 1);
        $service->findById(true);
    }

    //
    // Find All
    //

    public function testIfItSuccessfullyRetrievesAEmptyArrayFromFindAllEvenWithUnexistantRegisters()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $result = $service->findAll();

        $this->assertEmpty($result);
    }

    public function testIfItSuccessfullyRetrievesARegisterFromFindAllEvenWithUnexistantRegisters()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        $service->insert(1, 1);
        $result = $service->findAll();

        $this->assertNotEmpty($result);
    }

    public function testIfItSuccessfullyRetrievesAllTenRegistersFromFindAllEvenWithUnexistantRegisters()
    {
        $repository = new MockGamePlatformRepository();
        $service = new GamePlatformService($repository);

        for ($i = 1; $i <= 10; $i++) {
            $service->insert($i, $i);
        }
        $result = $service->findAll();

        $this->assertNotEmpty($result);
    }
}
