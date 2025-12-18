<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Game\Game;
use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGameRepository;
use PHPUnit\Framework\TestCase;

class GameServiceTest extends TestCase
{
    private GameRepositoryInterface $gameEntityRepository;
    private GameService $gameService;

    protected function setUp(): void
    {
        $this->gameEntityRepository = new MockGameRepository();
        $this->gameService = new GameService($this->gameEntityRepository);
    }

    public function testIfGameInsertionSucceds(): void
    {
        $name = "test";
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $this->assertInstanceOf(Game::class, $game);
    }

    public function testIfTenGameInsertionSucceds(): void
    {
        $name = "test";
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $game = $this->gameService->insert($name . $i, $isActive);
            $this->assertInstanceOf(Game::class, $game);
        }
    }

    public function testIfInsertionOfTwoGamesWithTheSameNameFails(): void
    {
        $name = "test";
        $isActive = true;

        $this->expectException(RepositoryDuplicatedEntryException::class);

        $this->gameService->insert($name, $isActive);
        $this->gameService->insert($name, $isActive);
    }

    public function testIfGameInsertionFailsWithEmptyName(): void
    {
        $this->expectException(EntityInvalidValueException::class);

        $name = "";
        $isActive = true;
        $this->gameService->insert($name, $isActive);
    }

    public function testIfUpdateSucceds(): void
    {
        $name = "test1";
        $isActive = true;
        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();
        $name = "test2";
        $hasUpdated = $this->gameService->update($id, $name, $isActive);

        $this->assertTrue($hasUpdated);
    }

    public function testIfUpdatingAGameWithAExistantNameSucceds(): void
    {
        $name = "test";
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();

        $hasUpdated = $this->gameService->update($id, $name, $isActive);

        $this->assertTrue($hasUpdated);
    }

    public function testIfUpdateSuccedsWithTenGamesInTheRepository(): void
    {
        $name = "test";
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

    public function testIfUpdatingAGameWithAInvalidIdFails(): void
    {
        $name = "test";
        $isActive = true;

        $this->gameService->insert($name, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gameService->update($id, $name, $isActive);
    }

    public function testIfUpdatingAGameWithAEmptyNameFails(): void
    {
        $name = "test";
        $emptyName = "";
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gameService->update($id, $emptyName, $isActive);
    }

    public function testIfSettingAsActiveSucceds(): void
    {
        $name = "test";
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();
        $isActive = false;

        $hasChanged = $this->gameService->setIsActive($id, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfSettingIsActiveWithSameValueDoesNotReallyChangesSomething(): void
    {
        $name = "test";
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();

        $hasChanged = $this->gameService->setIsActive($id, $isActive);

        $this->assertFalse($hasChanged);
    }

    public function testIfSettingIsActiveWithInvalidIdFails(): void
    {
        $name = "test";
        $isActive = true;

        $this->gameService->insert($name, $isActive);

        $this->expectException(EntityInvalidValueException::class);

        $id = -1;

        $this->gameService->setIsActive($id, $isActive);
    }

    public function testIfFindByIdSucceds(): void
    {
        $name = "test";
        $isActive = true;

        $game = $this->gameService->insert($name, $isActive);

        $id = $game->getId();

        $game = $this->gameService->findById($id);

        $this->assertNotEmpty($game);
        $this->assertInstanceOf(Game::class, $game);
    }

    public function testIfFindByIdSuccedsWithTenPlatforms(): void
    {
        $name = "test";
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

    public function testIfFindAllSuccedsEvenWithNoGamesInTheRepository(): void
    {
        $allGames = $this->gameService->findAll();

        $this->assertEmpty($allGames);
    }

    public function testIfFindAllSuccedsWithOneGameInTheRepository(): void
    {
        $name = "test";
        $isActive = true;

        $this->gameService->insert($name, $isActive);

        $result = $this->gameService->findAll();

        $this->assertNotEmpty($result);
    }

    public function testIfFindAllSuccedsWithTenGamesInTheRepository(): void
    {
        $name = "test";
        $isActive = true;

        $games = [];
        for ($i = 1; $i <= 10; $i++) {
            $games[$i] = $this->gameService->insert($name . $i, $isActive);
        }

        $allGames = $this->gameService->findAll();

        $this->assertNotEmpty($allGames);
    }
}
