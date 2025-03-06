<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGenreRepository;
use PHPUnit\Framework\TestCase;

class GenreServiceTest extends TestCase
{
    //
    // Insert
    //

    public function testIfGenreInsertionSuccedsWithTrueIsActive()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $genre = $genreService->insert('test', true);

        $this->assertInstanceOf(Genre::class, $genre);
    }

    public function testIfTenGenreInsertionSuccedsWithTrueIsActive()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectNotToPerformAssertions();

        for ($i = 1; $i <= 10; $i++) {
            $genreService->insert('test' . $i, true);
        }
    }

    public function testIfGenreInsertionSuccedsWithFalseIsActive()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $genre = $genreService->insert('test', false);

        $this->assertInstanceOf(Genre::class, $genre);
    }

    public function testIfTenGenreInsertionSuccedsWithFalseIsActive()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectNotToPerformAssertions();

        for ($i = 1; $i <= 10; $i++) {
            $genreService->insert('test' . $i, false);
        }
    }

    public function testIfInsertionOfTwoGenresWithTheSameNameFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(DatabaseDuplicatedEntryException::class);

        $genreService->insert('test', true);
        $genreService->insert('test', true);
    }

    //
    // Insert
    // - Name
    //

    public function testIfGenreInsertionFailsWithEmptyName()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('', true);
    }

    public function testIfGenreInsertionFailsWithNullName()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert(null, true);
    }

    public function testIfGenreInsertionFailsWithArrayName()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert([], true);
    }

    public function testIfGenreInsertionFailsWithNumberName()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert(1, true);
    }

    public function testIfGenreInsertionFailsWithBooleanName()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert(true, true);
    }

    //
    // Insert
    // - Is Active
    //

    public function testIfGenreInsertionFailsWithNullIsActive()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', null);
    }

    public function testIfGenreInsertionFailsWithArrayIsActive()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', []);
    }

    public function testIfGenreInsertionFailsWithStringIsActive()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', 'test');
    }

    public function testIfGenreInsertionFailsWithNumberIsActive()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', 1);
    }

    //
    // Update
    //

    public function testIfUpdateSuccedsWithOneGenreInTheRepository()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $genreService->insert('test', true);

        $this->assertTrue($genreService->update(1, 'test2', true));
    }

    public function testIfUpdateSuccedsWithTenGenresInTheRepository()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        for ($i = 1; $i <= 10; $i++) {
            $genreService->insert('test' . $i, true);
        }

        $this->assertTrue($genreService->update(1, 'test22', true));
    }

    public function testIfUpdatingAGenreWithAExistantNameFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(DatabaseDuplicatedEntryException::class);

        $genreService->insert('test', true);
        $genreService->update(1, 'test', true);
    }

    //
    // Update
    // - Id
    //

    public function testIfUpdatingAGenreWithAUnexistantIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(-1, 'test', true);
    }

    public function testIfUpdatingAGenreWithANullIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(null, 'test', true);
    }

    public function testIfUpdatingAGenreWithAArrayIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update([], 'test', true);
    }

    public function testIfUpdatingAGenreWithAStringIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update('test', 'test', true);
    }

    public function testIfUpdatingAGenreWithABooleanIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(true, 'test', true);
    }

    //
    // Update
    // - Name
    //

    public function testIfUpdatingAGenreWithAEmptyNameFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(1, '', true);
    }

    public function testIfUpdatingAGenreWithANullNameFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(1, null, true);
    }

    public function testIfUpdatingAGenreWithAArrayNameFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(1, [], true);
    }

    public function testIfUpdatingAGenreWithANumberNameFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(1, 1, true);
    }

    public function testIfUpdatingAGenreWithABooleanNameFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(1, true, true);
    }

    //
    // Update
    // - Is Active
    //

    public function testIfUpdatingAGenreWithANullIsActiveFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(1, 'test', null);
    }

    public function testIfUpdatingAGenreWithAArrayIsActiveFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(1, 'test', []);
    }

    public function testIfUpdatingAGenreWithAStringIsActiveFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(1, 'test', 'test');
    }

    public function testIfUpdatingAGenreWithANumberIsActiveFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->update(1, 'test', 1);
    }

    //
    // Set Is Active
    //

    public function testIfSettingAsActiveSucceds()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $genreService->insert('test', true);
        $this->assertTrue($genreService->setIsActive(1, false));
    }

    public function testIfSettingIsActiveWithSameValueFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $genreService->insert('test', true);
        $this->assertFalse($genreService->setIsActive(1, true));
    }

    //
    // Set Is Active
    // - Id
    //

    public function testIfSettingIsActiveWithUnexistantIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->setIsActive(-1, true);
    }

    public function testIfSettingIsActiveWithNullIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->setIsActive(null, true);
    }

    public function testIfSettingIsActiveWithArrayIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->setIsActive([], true);
    }

    public function testIfSettingIsActiveWithStringIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->setIsActive('test', true);
    }

    public function testIfSettingIsActiveWithBooleanIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->setIsActive(true, true);
    }

    //
    // Set Is Active
    // - Is Active
    //

    public function testIfSettingIsActiveWithNullValueFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->setIsActive(1, null);
    }

    public function testIfSettingIsActiveWithArrayValueFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->setIsActive(1, []);
    }

    public function testIfSettingIsActiveWithNumberValueFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->setIsActive(1, 1);
    }

    public function testIfSettingIsActiveWithStringValueFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->setIsActive(1, 'test');
    }

    //
    // Find By Id
    //

    public function testIfFindByIdSucceds()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $genreService->insert('test', true);
        $genre = $genreService->findById(1);

        $this->assertInstanceOf(Genre::class, $genre);
    }

    public function testIfFindByIdSuccedsWithTenPlatforms()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        for ($i = 1; $i <= 10; $i++) {
            $genreService->insert('test' . $i, true);
        }

        $genre = $genreService->findById(random_int(1, 10));

        $this->assertInstanceOf(Genre::class, $genre);
    }

    //
    // Find By Id
    // - Id
    //

    public function testIfFindByIdWithNullIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->findById(null);
    }

    public function testIfFindByIdWithArrayIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->findById([]);
    }

    public function testIfFindByIdWithStringIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->findById('test');
    }

    public function testIfFindByIdWithBooleanIdFails()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $this->expectException(EntityInvalidValueException::class);

        $genreService->insert('test', true);
        $genreService->findById('test');
    }

    //
    // Find All
    //

    public function testIfFindAllSuccedsEvenWithNoGenresInTheRepository()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $result = $genreService->findAll();

        $this->assertEmpty($result);
    }

    public function testIfFindAllSuccedsWithOneGenreInTheRepository()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        $genreService->insert('test', true);
        $result = $genreService->findAll();

        $this->assertNotEmpty($result);
    }

    public function testIfFindAllSuccedsWithTenGenresInTheRepository()
    {
        $genreRepository = new MockGenreRepository();
        $genreService = new GenreService($genreRepository);

        for ($i = 1; $i <= 10; $i++) {
            $genreService->insert('test' . $i, true);
        }
        $result = $genreService->findAll();

        $this->assertNotEmpty($result);
    }
}
