<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use ArrayIterator;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GamePlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGamePlatformRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockPlatformRepository;
use PHPUnit\Framework\TestCase;

class GamePlatformServiceTest extends TestCase
{
    private GameRepositoryInterface $gameRepository;
    private GameService $gameService;
    private PlatformRepositoryInterface $platformRepository;
    private PlatformService $platformService;
    private GamePlatformRepositoryInterface $gamePlatformRepository;
    private GamePlatformService $gamePlatformService;

    protected function setUp(): void
    {
        $this->gameRepository = new MockGameRepository();
        $this->gameService = new GameService($this->gameRepository);
        $this->platformRepository = new MockPlatformRepository();
        $this->platformService = new PlatformService($this->platformRepository);
        $this->gamePlatformRepository = new MockGamePlatformRepository(
            $this->gameRepository,
            $this->platformRepository
        );
        $this->gamePlatformService = new GamePlatformService($this->gamePlatformRepository);
    }

    public function testIfInsertSucceds(): void
    {
        $platform = $this->platformService->insert('test', true);
        $platformId = $platform->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

        $this->assertNotEmpty($gamePlatform);
        $this->assertInstanceOf(GamePlatform::class, $gamePlatform);
    }

    public function testIfTenInsertionsSucceds(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $platform = $this->platformService->insert('test'.$i, true);
            $platformId = $platform->getId();

            $game = $this->gameService->insert('test'.$i, true);
            $gameId = $game->getId();

            $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

            $this->assertNotEmpty($gamePlatform);
            $this->assertInstanceOf(GamePlatform::class, $gamePlatform);
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
        for ($i = 1; $i <= 2; $i++){
            $platforms[] = $this->platformService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
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
        for ($i = 1; $i <= 2; $i++){
            $platforms[] = $this->platformService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
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
        for ($i = 1; $i <= 2; $i++){
            $platforms[] = $this->platformService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
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

        $this->expectException(DatabaseUnexistantRegisterException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }    

    public function testIfUpdateWithInvalidGenreIdFails(): void
    {
        $platforms = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++){
            $platforms[] = $this->platformService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
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
        for ($i = 1; $i <= 2; $i++){
            $platforms[] = $this->platformService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
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

        $this->expectException(DatabaseUnexistantRegisterException::class);

        $this->gamePlatformService->update($id, $platformId, $gameId);
    }     

    public function testIfUpdateWithInvalidGameIdFails(): void
    {
        $platforms = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++){
            $platforms[] = $this->platformService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
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
        for ($i = 1; $i <= 2; $i++){
            $platforms[] = $this->platformService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
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

        $this->expectException(DatabaseUnexistantRegisterException::class);

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

        $fetchedGameGenre = $this->gamePlatformService->findById($id);

        $this->assertNotEmpty($fetchedGameGenre);
        $this->assertInstanceOf(GamePlatform::class, $fetchedGameGenre);
        $this->assertEquals($gamePlatform, $fetchedGameGenre);
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

        $fetchedGameGenre = $this->gamePlatformService->findById($id);

        $this->assertEmpty($fetchedGameGenre);
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
