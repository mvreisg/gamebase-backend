<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGamePlatformRepository;
use PHPUnit\Framework\TestCase;

class GamePlatformServiceTest extends TestCase
{
    private GamePlatformRepositoryInterface $gamePlatformRepository;
    private GamePlatformService $gamePlatformService;

    protected function setUp(): void
    {
        $this->gamePlatformRepository = new MockGamePlatformRepository();
        $this->gamePlatformService = new GamePlatformService($this->gamePlatformRepository);
    }

    public function testIfItSuccessfullyInserts()
    {
        $platformId = 1;
        $gameId = 1;

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

        $this->assertNotEmpty($gamePlatform);
        $this->assertInstanceOf(GamePlatform::class, $gamePlatform);
    }

    public function testIfItSuccessfullyInsertsTenRegisters()
    {
        $platformId = 1;
        $gameId = 1;

        for ($i = 1; $i <= 10; $i++) {
            $platformId = $i;
            $gameId = $i;

            $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

            $this->assertNotEmpty($gamePlatform);
            $this->assertInstanceOf(GamePlatform::class, $gamePlatform);
        }
    }

    public function testIfItFailsToInsertWithInvalidPlatformId()
    {
        $platformId = -1;
        $gameId = 1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->insert($platformId, $gameId);
    }

    public function testIfItFailsToInsertWithInvalidGameId()
    {
        $platformId = 1;
        $gameId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->insert($platformId, $gameId);
    }

    public function testIfItSuccessfullyUpdates()
    {
        $platformId = 1;
        $gameId = 1;

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

        $id = $gamePlatform->getId();

        $hasUpdated = $this->gamePlatformService->update($id, $platformId, $gameId);

        $this->assertTrue($hasUpdated);
    }

    public function testIfItSuccessfullyUpdatesWithTenRegisters()
    {
        $platformId = 1;
        $gameId = 1;

        $gamePlatforms = [];
        for ($i = 1; $i <= 10; $i++) {
            $platformId = $i;
            $gameId = $i;

            $gamePlatforms[$i] = $this->gamePlatformService->insert($platformId, $gameId);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $gamePlatforms[$i]->getId();

            $hasUpdated = $this->gamePlatformService->update($id, $platformId, $gameId);

            $this->assertTrue($hasUpdated);
        }
    }

    public function testIfItFailsToUpdateWithInvalidId()
    {
        $platformId = 1;
        $gameId = 1;

        $this->gamePlatformService->insert($platformId, $gameId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }

    public function testIfItFailsToUpdateWithInvalidPlatformId()
    {
        $platformId = 1;
        $gameId = 1;

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

        $platformId = -1;
        $id = $gamePlatform->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }

    public function testIfItFailsToUpdateWithInvalidGameId()
    {
        $platformId = 1;
        $gameId = 1;

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

        $gameId = -1;
        $id = $gamePlatform->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }

    public function testIfItSuccessfullyDeletes()
    {
        $platformId = 1;
        $gameId = 1;

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

        $id = $gamePlatform->getId();

        $hasDeleted = $this->gamePlatformService->delete($id);

        $this->assertTrue($hasDeleted);
    }

    public function testIfItSuccessfullyDeletesWithTenRegisters()
    {
        $platformId = 1;
        $gameId = 1;

        $gamePlatforms = [];
        for ($i = 1; $i <= 10; $i++) {
            $platformId = $i;
            $gameId = $i;

            $gamePlatforms[$i] = $this->gamePlatformService->insert($platformId, $gameId);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $gamePlatforms[$i]->getId();

            $hasDeleted = $this->gamePlatformService->delete($id);

            $this->assertTrue($hasDeleted);
        }
    }

    public function testIfItFailsToDeleteWithInvalidId()
    {
        $platformId = 1;
        $gameId = 1;

        $this->gamePlatformService->insert($platformId, $gameId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->delete($id);
    }

    public function testIfItSucessfullyFindsById()
    {
        $platformId = 1;
        $gameId = 1;

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

        $id = $gamePlatform->getId();

        $fetchedGamePlatform = $this->gamePlatformService->findById($id);

        $this->assertNotEmpty($fetchedGamePlatform);
        $this->assertInstanceOf(GamePlatform::class, $fetchedGamePlatform);
        $this->assertEquals($gamePlatform, $fetchedGamePlatform);
    }

    public function testIfItSucessfullyFindsByIdWithTenRegisters()
    {
        $platformId = 1;
        $gameId = 1;

        $gamePlatforms = [];
        for ($i = 1; $i <= 10; $i++) {
            $gamePlatforms[$i] = $this->gamePlatformService->insert($platformId, $gameId);
        }

        for ($i = 1; $i <= 10; $i++) {
            $registeredGamePlatform = $gamePlatforms[$i];
            $id = $registeredGamePlatform->getId();

            $fetchedGamePlatform = $this->gamePlatformService->findById($id);

            $this->assertNotEmpty($fetchedGamePlatform);
            $this->assertInstanceOf(GamePlatform::class, $fetchedGamePlatform);
            $this->assertEquals($registeredGamePlatform, $fetchedGamePlatform);
        }
    }

    public function testIfItFailsToFindByIdWithInvalidId()
    {
        $platformId = 1;
        $gameId = 1;

        $this->gamePlatformService->insert($platformId, $gameId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->findById($id);
    }

    public function testIfItSuccessfullyRetrievesAEmptyArrayFromFindAll()
    {
        $emptyArray = $this->gamePlatformService->findAll();

        $this->assertEmpty($emptyArray);
    }

    public function testIfItSuccessfullyRetrievesFromFindAll()
    {
        $platformId = 1;
        $gameId = 1;

        $this->gamePlatformService->insert($platformId, $gameId);

        $emptyArray = $this->gamePlatformService->findAll();

        $this->assertNotEmpty($emptyArray);
    }
}
