<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGameRepository;
use PHPUnit\Framework\TestCase;

class GameServiceTest extends TestCase
{
    private GameRepositoryInterface $gameRepository;
    private GameService $gameService;

    protected function setUp(): void
    {
        $this->gameRepository = new MockGameRepository();
        $this->gameService = new GameService($this->gameRepository);
    }

    public function testIfGameInsertionSucceds()
    {
        $name = 'test';
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $this->assertInstanceOf(Game::class, $game);
    }

    public function testIfTenGameInsertionSucceds()
    {
        $name = 'test';
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $game = $this->gameService->insert($name . $i, $isActive);
            $this->assertInstanceOf(Game::class, $game);
        }
    }

    public function testIfInsertionOfTwoGamesWithTheSameNameFails()
    {
        $name = 'test';
        $isActive = true;

        $this->expectException(DatabaseDuplicatedEntryException::class);

        $this->gameService->insert($name, $isActive);
        $this->gameService->insert($name, $isActive);
    }

    public function testIfGameInsertionFailsWithEmptyName()
    {
        $this->expectException(EntityInvalidValueException::class);

        $name = '';
        $isActive = true;
        $this->gameService->insert($name, $isActive);
    }

    public function testIfUpdateSucceds()
    {
        $name = 'test1';
        $isActive = true;
        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();
        $name = 'test2';
        $hasUpdated = $this->gameService->update($id, $name, $isActive);

        $this->assertTrue($hasUpdated);
    }

    public function testIfUpdatingAGameWithAExistantNameSucceds()
    {
        $name = 'test';
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();

        $hasUpdated = $this->gameService->update($id, $name, $isActive);

        $this->assertTrue($hasUpdated);
    }

    public function testIfUpdateSuccedsWithTenGamesInTheRepository()
    {
        $name = 'test';
        $isActive = true;

        $games = [];
        for ($i = 1; $i <= 10; $i++) {
            $games[$i] = $this->gameService->insert($name . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $games[$i]->getId();
            $hasUpdated = $this->gameService->update($id, $name . $i, $isActive);
            $this->assertTrue($hasUpdated);
        }
    }

    public function testIfUpdatingAGameWithAInvalidIdFails()
    {
        $name = 'test';
        $isActive = true;

        $this->gameService->insert($name, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gameService->update($id, $name, $isActive);
    }

    public function testIfUpdatingAGameWithAEmptyNameFails()
    {
        $name = 'test';
        $emptyName = '';
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gameService->update($id, $emptyName, $isActive);
    }

    public function testIfSettingAsActiveSucceds()
    {
        $name = 'test';
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();
        $isActive = false;

        $hasChanged = $this->gameService->setIsActive($id, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfSettingIsActiveWithSameValueDoesNotReallyChangesSomething()
    {
        $name = 'test';
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();

        $hasChanged = $this->gameService->setIsActive($id, $isActive);

        $this->assertFalse($hasChanged);
    }

    public function testIfSettingIsActiveWithInvalidIdFails()
    {
        $name = 'test';
        $isActive = true;

        $this->gameService->insert($name, $isActive);

        $this->expectException(EntityInvalidValueException::class);

        $id = -1;

        $this->gameService->setIsActive($id, $isActive);
    }

    public function testIfFindByIdSucceds()
    {
        $name = 'test';
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();

        $game = $this->gameService->findById($id);

        $this->assertNotEmpty($game);
        $this->assertInstanceOf(Game::class, $game);
    }

    public function testIfFindByIdSuccedsWithTenPlatforms()
    {
        $name = 'test';
        $isActive = true;

        $games = [];
        for ($i = 1; $i <= 10; $i++) {
            $games[$i] = $this->gameService->insert($name . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $game = $this->gameService->findById($i);
            $this->assertNotEmpty($game);
            $this->assertInstanceOf(Game::class, $game);
        }
    }

    public function testIfFindAllSuccedsEvenWithNoGamesInTheRepository()
    {
        $allGames = $this->gameService->findAll();

        $this->assertEmpty($allGames);
    }

    public function testIfFindAllSuccedsWithOneGameInTheRepository()
    {
        $name = 'test';
        $isActive = true;

        $this->gameService->insert($name, $isActive);

        $result = $this->gameService->findAll();

        $this->assertNotEmpty($result);
    }

    public function testIfFindAllSuccedsWithTenGamesInTheRepository()
    {
        $name = 'test';
        $isActive = true;

        $games = [];
        for ($i = 1; $i <= 10; $i++) {
            $games[$i] = $this->gameService->insert($name . $i, $isActive);
        }

        $allGames = $this->gameService->findAll();

        $this->assertNotEmpty($allGames);
    }
}
