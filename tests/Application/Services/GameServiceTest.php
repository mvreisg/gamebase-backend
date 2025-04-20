<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGameRepository;
use PHPUnit\Framework\TestCase;

class GameServiceTest extends TestCase
{
    //
    // Insert
    //

    public function testIfGameInsertionSuccedsWithTrueIsActive()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $game = $gameService->insert('test', true);

        $this->assertInstanceOf(Game::class, $game);
    }

    public function testIfTenGameInsertionSuccedsWithTrueIsActive()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectNotToPerformAssertions();

        for ($i = 1; $i <= 10; $i++) {
            $gameService->insert('test' . $i, true);
        }
    }

    public function testIfGameInsertionSuccedsWithFalseIsActive()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $game = $gameService->insert('test', false);

        $this->assertInstanceOf(Game::class, $game);
    }

    public function testIfTenGameInsertionSuccedsWithFalseIsActive()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectNotToPerformAssertions();

        for ($i = 1; $i <= 10; $i++) {
            $gameService->insert('test' . $i, false);
        }
    }

    public function testIfInsertionOfTwoGamesWithTheSameNameFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(DatabaseDuplicatedEntryException::class);

        $gameService->insert('test', true);
        $gameService->insert('test', true);
    }

    //
    // Insert
    // - Name
    //

    public function testIfGameInsertionFailsWithEmptyName()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('', true);
    }

    public function testIfGameInsertionFailsWithNullName()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert(null, true);
    }

    public function testIfGameInsertionFailsWithArrayName()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert([], true);
    }

    public function testIfGameInsertionFailsWithNumberName()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert(1, true);
    }

    public function testIfGameInsertionFailsWithBooleanName()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert(true, true);
    }

    //
    // Insert
    // - Is Active
    //

    public function testIfGameInsertionFailsWithNullIsActive()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', null);
    }

    public function testIfGameInsertionFailsWithArrayIsActive()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', []);
    }

    public function testIfGameInsertionFailsWithStringIsActive()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', 'test');
    }

    public function testIfGameInsertionFailsWithNumberIsActive()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', 1);
    }

    //
    // Update
    //

    public function testIfUpdateSuccedsWithOneGameInTheRepository()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $gameService->insert('test', true);

        $this->assertTrue($gameService->update(1, 'test2', true));
    }

    public function testIfUpdateSuccedsWithTenGamesInTheRepository()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        for ($i = 1; $i <= 10; $i++) {
            $gameService->insert('test' . $i, true);
        }

        $this->assertTrue($gameService->update(1, 'test22', true));
    }

    public function testIfUpdatingAGameWithAExistantNameSucceds()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectNotToPerformAssertions();

        $gameService->insert('test', true);
        $gameService->update(1, 'test', true);
    }

    //
    // Update
    // - Id
    //

    public function testIfUpdatingAGameWithAUnexistantIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(-1, 'test', true);
    }

    public function testIfUpdatingAGameWithANullIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(null, 'test', true);
    }

    public function testIfUpdatingAGameWithAArrayIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update([], 'test', true);
    }

    public function testIfUpdatingAGameWithAStringIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update('test', 'test', true);
    }

    public function testIfUpdatingAGameWithABooleanIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(true, 'test', true);
    }

    //
    // Update
    // - Name
    //

    public function testIfUpdatingAGameWithAEmptyNameFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(1, '', true);
    }

    public function testIfUpdatingAGameWithANullNameFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(1, null, true);
    }

    public function testIfUpdatingAGameWithAArrayNameFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(1, [], true);
    }

    public function testIfUpdatingAGameWithANumberNameFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(1, 1, true);
    }

    public function testIfUpdatingAGameWithABooleanNameFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(1, true, true);
    }

    //
    // Update
    // - Is Active
    //

    public function testIfUpdatingAGameWithANullIsActiveFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(1, 'test', null);
    }

    public function testIfUpdatingAGameWithAArrayIsActiveFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(1, 'test', []);
    }

    public function testIfUpdatingAGameWithAStringIsActiveFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(1, 'test', 'test');
    }

    public function testIfUpdatingAGameWithANumberIsActiveFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->update(1, 'test', 1);
    }

    //
    // Set Is Active
    //

    public function testIfSettingAsActiveSucceds()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $gameService->insert('test', true);
        $this->assertTrue($gameService->setIsActive(1, false));
    }

    public function testIfSettingIsActiveWithSameValueFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $gameService->insert('test', true);
        $this->assertFalse($gameService->setIsActive(1, true));
    }

    //
    // Set Is Active
    // - Id
    //

    public function testIfSettingIsActiveWithUnexistantIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->setIsActive(-1, true);
    }

    public function testIfSettingIsActiveWithNullIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->setIsActive(null, true);
    }

    public function testIfSettingIsActiveWithArrayIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->setIsActive([], true);
    }

    public function testIfSettingIsActiveWithStringIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->setIsActive('test', true);
    }

    public function testIfSettingIsActiveWithBooleanIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->setIsActive(true, true);
    }

    //
    // Set Is Active
    // - Is Active
    //

    public function testIfSettingIsActiveWithNullValueFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->setIsActive(1, null);
    }

    public function testIfSettingIsActiveWithArrayValueFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->setIsActive(1, []);
    }

    public function testIfSettingIsActiveWithNumberValueFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->setIsActive(1, 1);
    }

    public function testIfSettingIsActiveWithStringValueFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->setIsActive(1, 'test');
    }

    //
    // Find By Id
    //

    public function testIfFindByIdSucceds()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $gameService->insert('test', true);
        $game = $gameService->findById(1);

        $this->assertInstanceOf(Game::class, $game);
    }

    public function testIfFindByIdSuccedsWithTenPlatforms()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        for ($i = 1; $i <= 10; $i++) {
            $gameService->insert('test' . $i, true);
        }

        $game = $gameService->findById(random_int(1, 10));

        $this->assertInstanceOf(Game::class, $game);
    }

    //
    // Find By Id
    // - Id
    //

    public function testIfFindByIdWithNullIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->findById(null);
    }

    public function testIfFindByIdWithArrayIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->findById([]);
    }

    public function testIfFindByIdWithStringIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->findById('test');
    }

    public function testIfFindByIdWithBooleanIdFails()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $this->expectException(EntityInvalidValueException::class);

        $gameService->insert('test', true);
        $gameService->findById('test');
    }

    //
    // Find All
    //

    public function testIfFindAllSuccedsEvenWithNoGamesInTheRepository()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $result = $gameService->findAll();

        $this->assertEmpty($result);
    }

    public function testIfFindAllSuccedsWithOneGameInTheRepository()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        $gameService->insert('test', true);
        $result = $gameService->findAll();

        $this->assertNotEmpty($result);
    }

    public function testIfFindAllSuccedsWithTenGamesInTheRepository()
    {
        $gameRepository = new MockGameRepository();
        $gameService = new GameService($gameRepository);

        for ($i = 1; $i <= 10; $i++) {
            $gameService->insert('test' . $i, true);
        }
        $result = $gameService->findAll();

        $this->assertNotEmpty($result);
    }
}
