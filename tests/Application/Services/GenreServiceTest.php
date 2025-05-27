<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGenreRepository;
use PHPUnit\Framework\TestCase;

class GenreServiceTest extends TestCase
{
    private GenreRepositoryInterface $genreRepository;
    private GenreService $genreService;

    protected function setUp(): void
    {
        $this->genreRepository = new MockGenreRepository();
        $this->genreService = new GenreService($this->genreRepository);
    }

    public function testIfGenreInsertionSucceds()
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $this->assertNotEmpty($genre);
        $this->assertInstanceOf(Genre::class, $genre);
    }

    public function testIfTenGenreInsertionSucceds()
    {
        $name = 'test';
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $genre = $this->genreService->insert($name.$i, $isActive);
            $this->assertNotEmpty($genre);
            $this->assertInstanceOf(Genre::class, $genre);
        }
    }

    public function testIfInsertionOfTwoGenresWithTheSameNameFails()
    {
        $this->expectException(DatabaseDuplicatedEntryException::class);

        $name = 'test';
        $isActive = true;

        $this->genreService->insert($name, $isActive);
        $this->genreService->insert($name, $isActive);
    }

    public function testIfGenreInsertionFailsWithEmptyName()
    {
        $name = '';
        $isActive = true;

        $this->expectException(EntityInvalidValueException::class);

        $this->genreService->insert($name, $isActive);
    }

    public function testIfUpdateSuccedsWithOneGenreInTheRepository()
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $name = 'test2';
        $isActive = false;
        $id = $genre->getId();

        $hasUpdated = $this->genreService->update($id, $name, $isActive);

        $this->assertTrue($hasUpdated);
    }

    public function testIfUpdateSuccedsWithTenGenresInTheRepository()
    {
        $name = 'test';
        $isActive = true;

        $genres = [];
        for ($i = 1; $i <= 10; $i++){
            $genres[$i] = $this->genreService->insert($name.$i, $isActive);
        }
        
        for ($i = 1; $i <= 10; $i++){
            $id = $genres[$i]->getId();
            $hasUpdated = $this->genreService->update($id, $name.$i*10, $isActive);

            $this->assertTrue($hasUpdated);
        }
    }

    public function testIfUpdatingAGenreWithAExistantNameSucceds()
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $id = $genre->getId();

        $hasUpdated = $this->genreService->update($id, $name, $isActive);

        $this->assertTrue($hasUpdated);
    }

    public function testIfUpdatingAGenreWithInvalidIdFails()
    {
        $name = 'test';
        $isActive = true;

        $this->genreService->insert($name, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->genreService->update($id, $name, $isActive);
    }

    public function testIfUpdatingAGenreWithAEmptyNameFails()
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $id = $genre->getId();
        $name = '';

        $this->expectException(EntityInvalidValueException::class);

        $this->genreService->update($id, $name, $isActive);
    }

    public function testIfSettingAsActiveSucceds()
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $id = $genre->getId();
        $isActive = false;

        $hasChanged = $this->genreService->setIsActive($id, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfSettingIsActiveWithSameValueFails()
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $id = $genre->getId();

        $hasChanged = $this->genreService->setIsActive($id, $isActive);

        $this->assertFalse($hasChanged);
    }

    public function testIfSettingIsActiveWitInvalidIdFails()
    {
        $name = 'test';
        $isActive = true;

        $this->genreService->insert($name, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->genreService->setIsActive($id, $isActive);
    }

    public function testIfFindByIdSucceds()
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $id = $genre->getId();

        $genre = $this->genreService->findById($id);

        $this->assertNotEmpty($genre);
        $this->assertInstanceOf(Genre::class, $genre);
    }

    public function testIfFindByIdSuccedsWithTenGenres()
    {
        $name = 'test';
        $isActive = true;

        $genres = [];
        for ($i = 1; $i <= 10; $i++){
            $genres[$i] = $this->genreService->insert($name.$i, $isActive);
        }        

        for ($i = 1; $i <= 10; $i++){
            $id = $genres[$i]->getId();

            $genre = $this->genreService->findById($id);

            $this->assertNotEmpty($genre);
            $this->assertInstanceOf(Genre::class, $genre);
        }
    }

    public function testIfFindAllSuccedsEvenWithNoGenresInTheRepository()
    {
        $emptyArray = $this->genreService->findAll();

        $this->assertEmpty($emptyArray);
    }

    public function testIfFindAllSuccedsWithOneGenreInTheRepository()
    {
        $name = 'test';
        $isActive = true;

        $this->genreService->insert($name, $isActive);

        $genresArray = $this->genreService->findAll();

        $this->assertNotEmpty($genresArray);
    }

    public function testIfFindAllSuccedsWithTenGenresInTheRepository()
    {
        $name = 'test';
        $isActive = true;

        for ($i = 1; $i <= 10; $i++){
            $this->genreService->insert($name.$i, $isActive);
        }        

        $genresArray = $this->genreService->findAll();

        $this->assertNotEmpty($genresArray);
    }
}
