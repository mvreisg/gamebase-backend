<?php
    namespace Gamebase\Infrastructure\Repositories;

    use PDO;
    use PDOException;
    use Gamebase\Domain\Entities\Genre;
    use Gamebase\Domain\Repositories\GenreRepositoryInterface; 
    
	include_once("./../src/domain/repositories/GenreRepositoryInterface.php");

    class MariaDBGenreRepository implements GenreRepositoryInterface 
    {
        private PDO $pdo;

        public function __construct(PDO $pdo) 
        {
            $this->pdo = $pdo;
        }

        public function insert(Genre $genre): Genre 
        {
            try
            {
                $this->pdo->beginTransaction();

                $name = $genre->getName();

                $insertStatement = $this->pdo->prepare("INSERT INTO genre (name) VALUES (:name);");
                $insertStatement->execute([
                    ":name" => $genre->getName(),
                ]);                

                $lastInsertId = intval($this->pdo->lastInsertId());

                $selectGameStatement = $this->pdo->prepare("SELECT * FROM genre WHERE id = :id;");
                $selectGameStatement->execute([
                    ":id" => $lastInsertId
                ]);

                $genreFetchResult = $selectGameStatement->fetch();

                $this->pdo->commit();

                $newGenre = new Genre();
                $newGenre->setId($genreFetchResult["id"]);
                $newGenre->setName($genreFetchResult["name"]);

                return $newGenre;
            }
            catch (PDOException $e)
            {
                $this->pdo->rollBack();
                throw $e;
            }
        }

        public function edit(Genre $genre): bool 
        {
            try 
            {
                $name = $genre->getName();
                
                $statement = $this->pdo->prepare(
                    "UPDATE
                        genre
                    SET
                        name = :name
                    WHERE
                        id = :id;"
                );

                $wasItSuccessful = $statement->execute([
                    ":name" => $genre->getName(),
                    ":id" => $genre->getId()
                ]);

                return $wasItSuccessful;
            }
            catch (PDOException $e) 
            {
                throw $e;
            }
        }

        public function delete(int $id): bool 
        {
            return false;
        }

        public function findById(int $id): Genre|null
        {
            try
            {
                $statement = $this->pdo->prepare("SELECT * FROM genre WHERE id = :id;");
                $statement->execute([
                    ":id" => $id
                ]);
                
                $result = $statement->fetch();
                if ($result === false)
                {
                    return null;
                }

                $genre = new Genre();
                $genre->setId($result["id"]);
                $genre->setName($result["name"]);
                return $genre;
            }
            catch (PDOException $e)
            {
                throw $e;
            } 
        }

        public function findAll(): array 
        {
            try
            {
                $statement = $this->pdo->prepare("SELECT * FROM genre;");
                $statement->execute();
                
                $result = $statement->fetchAll();

                $genres = [];

                foreach ($result as $row) 
                {
                    $genre = new Genre();
                    $genre->setId($row["id"]);
                    $genre->setName($row["name"]);
                    $genres[] = $genre;
                }

                return $genres;
            }
            catch (PDOException $e)
            {
                throw $e;
            }            
        }

        public function hasDuplicatedNames(string $name): bool
        {
            try 
            {
                $statement = $this->pdo->prepare("SELECT * FROM genre WHERE name = :name;");
    
                $statement->execute([
                    ":name" => $name
                ]);
    
                $result = $statement->fetch();
    
                return $result == true;
            }
            catch (PDOException $e)
            {
                throw $e;
            }
        }
    }
?>