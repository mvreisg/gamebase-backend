<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use ArrayIterator;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatformEntity;
use Mvreisg\GamebaseBackend\Domain\Exceptions\Entities\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGameEntityRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGamePlatformEntityRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockPlatformEntityRepository;
use PHPUnit\Framework\TestCase;

class GamePlatformServiceTest extends TestCase
{
    private GameEntityRepositoryInterface $gameEntityRepository;
    private GameService $gameService;
    private PlatformEntityRepositoryInterface $platformEntityRepository;
    private PlatformService $platformService;
    private GamePlatformEntityRepositoryInterface $gamePlatformEntityRepository;
    private GamePlatformService $gamePlatformService;

    protected function setUp(): void
    {
        $this->gameEntityRepository = new MockGameEntityRepository();
        $this->gameService = new GameService($this->gameEntityRepository);
        $this->platformEntityRepository = new MockPlatformEntityRepository();
        $this->platformService = new PlatformService($this->platformEntityRepository);
        $this->gamePlatformEntityRepository = new MockGamePlatformEntityRepository(
            $this->gameEntityRepository,
            $this->platformEntityRepository
        );
        $this->gamePlatformService = new GamePlatformService($this->gamePlatformEntityRepository);
    }

    public function testIfInsertSucceds(): void
    {
        $platform = $this->platformService->insert('test', true);
        $platformId = $platform->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

        $this->assertNotEmpty($gamePlatform);
        $this->assertInstanceOf(GamePlatformEntity::class, $gamePlatform);
    }

    public function testIfTenInsertionsSucceds(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $platform = $this->platformService->insert('test' . $i, true);
            $platformId = $platform->getId();

            $game = $this->gameService->insert('test' . $i, true);
            $gameId = $game->getId();

            $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

            $this->assertNotEmpty($gamePlatform);
            $this->assertInstanceOf(GamePlatformEntity::class, $gamePlatform);
        }
    }

    public function testIfInsertWithInvalidGenreIdFails(): void
    {
        $platformId = -1;

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->insert($platformId, $gameId);
    }

    public function testIfInsertWithInvalidGameIdFails(): void
    {
        $platform = $this->platformService->insert('test', true);
        $platformId = $platform->getId();

        $gameId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->insert($platformId, $gameId);
    }

    public function testIfUpdateSucceds(): void
    {
        $platforms = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++) {
            $platforms[] = $this->platformService->insert('test' . $i, true);
            $games[] = $this->gameService->insert('test' . $i, true);
        }

        $platformsIterator = new ArrayIterator($platforms);
        $gamesIterator = new ArrayIterator($games);

        $platform = $platformsIterator->current();
        $platformsIterator->next();
        $platformId = $platform->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);
        $id = $gamePlatform->getId();

        $platform = $platformsIterator->current();
        $platformId = $platform->getId();

        $game = $gamesIterator->current();
        $gameId = $game->getId();

        $hasUpdated = $this->gamePlatformService->update($id, $platformId, $gameId);

        $this->assertTrue($hasUpdated);
    }

    public function testIfUpdateWithInvalidIdFails(): void
    {
        $platforms = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++) {
            $platforms[] = $this->platformService->insert('test' . $i, true);
            $games[] = $this->gameService->insert('test' . $i, true);
        }

        $platformsIterator = new ArrayIterator($platforms);
        $gamesIterator = new ArrayIterator($games);

        $platform = $platformsIterator->current();
        $platformsIterator->next();
        $platformId = $platform->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $this->gamePlatformService->insert($platformId, $gameId);
        $id = -1;

        $platform = $platformsIterator->current();
        $platformId = $platform->getId();

        $game = $gamesIterator->current();
        $gameId = $game->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }

    public function testIfUpdateWithUnexistantIdFails(): void
    {
        $platforms = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++) {
            $platforms[] = $this->platformService->insert('test' . $i, true);
            $games[] = $this->gameService->insert('test' . $i, true);
        }

        $platformsIterator = new ArrayIterator($platforms);
        $gamesIterator = new ArrayIterator($games);

        $platform = $platformsIterator->current();
        $platformsIterator->next();
        $platformId = $platform->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $this->gamePlatformService->insert($platformId, $gameId);
        $id = 999;

        $platform = $platformsIterator->current();
        $platformId = $platform->getId();

        $game = $gamesIterator->current();
        $gameId = $game->getId();

        $this->expectException(MockUnexistantRegisterException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }

    public function testIfUpdateWithInvalidGenreIdFails(): void
    {
        $platforms = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++) {
            $platforms[] = $this->platformService->insert('test' . $i, true);
            $games[] = $this->gameService->insert('test' . $i, true);
        }

        $platformsIterator = new ArrayIterator($platforms);
        $gamesIterator = new ArrayIterator($games);

        $platform = $platformsIterator->current();
        $platformsIterator->next();
        $platformId = $platform->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);
        $id = $gamePlatform->getId();

        $platformId = -1;

        $game = $gamesIterator->current();
        $gameId = $game->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }

    public function testIfUpdateWithUnexistantGenreIdFails(): void
    {
        $platforms = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++) {
            $platforms[] = $this->platformService->insert('test' . $i, true);
            $games[] = $this->gameService->insert('test' . $i, true);
        }

        $platformsIterator = new ArrayIterator($platforms);
        $gamesIterator = new ArrayIterator($games);

        $platform = $platformsIterator->current();
        $platformsIterator->next();
        $platformId = $platform->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);
        $id = $gamePlatform->getId();

        $platformId = 999;

        $game = $gamesIterator->current();
        $gameId = $game->getId();

        $this->expectException(MockUnexistantRegisterException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }

    public function testIfUpdateWithInvalidGameIdFails(): void
    {
        $platforms = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++) {
            $platforms[] = $this->platformService->insert('test' . $i, true);
            $games[] = $this->gameService->insert('test' . $i, true);
        }

        $platformsIterator = new ArrayIterator($platforms);
        $gamesIterator = new ArrayIterator($games);

        $platform = $platformsIterator->current();
        $platformsIterator->next();
        $platformId = $platform->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);
        $id = $gamePlatform->getId();

        $platform = $platformsIterator->current();
        $platformId = $platform->getId();

        $gameId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }

    public function testIfUpdateWithUnexistantGameIdFails(): void
    {
        $platforms = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++) {
            $platforms[] = $this->platformService->insert('test' . $i, true);
            $games[] = $this->gameService->insert('test' . $i, true);
        }

        $platformsIterator = new ArrayIterator($platforms);
        $gamesIterator = new ArrayIterator($games);

        $platform = $platformsIterator->current();
        $platformsIterator->next();
        $platformId = $platform->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);
        $id = $gamePlatform->getId();

        $platform = $platformsIterator->current();
        $platformId = $platform->getId();

        $gameId = 999;

        $this->expectException(MockUnexistantRegisterException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }

    public function testIfDeleteSucceds(): void
    {
        $platform = $this->platformService->insert('test', true);
        $platformId = $platform->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);
        $id = $gamePlatform->getId();

        $hasDeleted = $this->gamePlatformService->delete($id);

        $this->assertTrue($hasDeleted);
    }

    public function testIfDeleteWithInvalidIdFails(): void
    {
        $platform = $this->platformService->insert('test', true);
        $platformId = $platform->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->gamePlatformService->insert($platformId, $gameId);
        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->delete($id);
    }

    public function testIfDeleteWithUnexistantIdFails(): void
    {
        $platform = $this->platformService->insert('test', true);
        $platformId = $platform->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->gamePlatformService->insert($platformId, $gameId);
        $id = 999;

        $hasDeleted = $this->gamePlatformService->delete($id);

        $this->assertFalse($hasDeleted);
    }

    public function testIfFindByIdSucceds(): void
    {
        $platform = $this->platformService->insert('test', true);
        $platformId = $platform->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);
        $id = $gamePlatform->getId();

        $fetchedGameGenreEntity = $this->gamePlatformService->findById($id);

        $this->assertNotEmpty($fetchedGameGenreEntity);
        $this->assertInstanceOf(GamePlatformEntity::class, $fetchedGameGenreEntity);
        $this->assertEquals($gamePlatform, $fetchedGameGenreEntity);
    }

    public function testIfFindByIdWithInvalidIdFails(): void
    {
        $platform = $this->platformService->insert('test', true);
        $platformId = $platform->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->gamePlatformService->insert($platformId, $gameId);
        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gamePlatformService->findById($id);
    }

    public function testIfFindByIdWithUnexistantIdFails(): void
    {
        $platform = $this->platformService->insert('test', true);
        $platformId = $platform->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->gamePlatformService->insert($platformId, $gameId);
        $id = 999;

        $fetchedGameGenreEntity = $this->gamePlatformService->findById($id);

        $this->assertEmpty($fetchedGameGenreEntity);
    }

    public function testIfFindAllSucceds(): void
    {
        $platform = $this->platformService->insert('test', true);
        $platformId = $platform->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->gamePlatformService->insert($platformId, $gameId);

        $gameGenres = $this->gamePlatformService->findAll();

        $this->assertNotEmpty($gameGenres);
    }

    public function testIfFindAllWithNoRegistersSucceds(): void
    {
        $gameGenres = $this->gamePlatformService->findAll();

        $this->assertEmpty($gameGenres);
    }
}
